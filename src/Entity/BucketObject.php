<?php

namespace AntonAm\DigitalOcean\Spaces\Entity;

use AntonAm\DigitalOcean\Spaces\EntityInterface;
use AntonAm\DigitalOcean\Spaces\Manager as SpacesManager;
use Aws\S3\S3Client;

abstract class BucketObject implements EntityInterface
{
    /** @var S3Client */
    protected $client;
    protected $bucket;
    protected $object;

    public function __construct(SpacesManager $manager, $object)
    {
        $this->client = $manager->getClient();
        $this->bucket = $manager->getSpace();
        $this->object = $object;
    }

    public function get()
    {
        $result = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $this->object,
        ]);

        return $result->toArray();
    }

    public function create()
    {

    }


    public function download($path): bool
    {
        $result = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $this->object,
        ]);

        return $result->toArray();
    }


    public function delete(): bool
    {
        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key'    => $this->object,
        ]);

        $this->client->waitUntil('ObjectNotExists', [
            'Bucket' => $this->bucket,
            'Key'    => $this->object
        ]);

        return true;
    }


    public function deleteMatching(): bool
    {
        $this->client->deleteMatchingObjects(
            $this->bucket,
            $this->object
        );

        $this->client->waitUntil('ObjectNotExists', [
            'Bucket' => $this->bucket,
            'Key'    => $this->object
        ]);

        return true;
    }

    public function exist(): bool
    {
        return $this->client->doesObjectExist($this->bucket, $this->object);
    }

    public function getAcl()
    {
        $result = $this->client->getObjectAcl([
            'Bucket' => $this->bucket,
            'Key'    => $this->object,
        ]);
        return $result->toArray();
    }

    public function putAcl($acl): array
    {
        $acl = array_merge(['Bucket' => $this->bucket, 'Key' => $this->object], $acl);
        $result = $this->client->putObjectAcl($acl);
        return $result->toArray();
    }

    public function makePrivate(): bool
    {
        return !empty($this->putAcl([['ACL' => 'private']]));
    }

    public function makePublic(): bool
    {
        return !empty($this->putAcl([['ACL' => 'public-read']]));
    }
}