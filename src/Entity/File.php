<?php

namespace AntonAm\DigitalOcean\Spaces\Entity;


/**
 * Class File
 *
 * @package AntonAm\DigitalOcean\Spaces\Entity
 */
class File extends BucketObject
{
    private $file;
    private $access = 'private';
    private $mimeType = 'application/octet-stream';

    public function setFile($data): self
    {
        if (file_exists($data) && is_file($data)) {
            $this->file = file_get_contents($data);
        } else {
            $this->file = $data;
        }

        return $this;
    }

    public function setAccess($access): self
    {
        $access = $access === 'public' ? 'public-read' : $access;

        if (in_array($access, ['private', 'public-read'], false)) {
            $this->access = $access;
        }

        return $this;
    }

    public function setMimeType($type): self
    {
        $this->mimeType = $type;

        return $this;
    }

    public function create()
    {
        $result = $this->client->putObject([
            'Bucket'      => $this->bucket,
            'Key'         => $this->object,
            'Body'        => $this->file,
            'ACL'         => $this->access,
            'ContentType' => $this->mimeType
        ]);

        $this->client->waitUntil('ObjectExists', [
            'Bucket' => $this->bucket,
            'Key'    => $this->object
        ]);

        return $result->toArray();
    }

    public function createTempUrl($validFor = '1 hour'): string
    {
        $cmd = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key'    => $validFor
        ]);
        $request = $this->client->createPresignedRequest($cmd, $validFor);

        return (string)$request->getUri();
    }
}