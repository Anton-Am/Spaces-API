<?php

namespace AntonAm\DigitalOcean\Spaces\Entity;

/**
 * Class Directory
 *
 * @package AntonAm\DigitalOcean\Spaces\Entity
 */
class Directory extends BucketObject
{
    public function files(): array
    {
        $request = $this->client->getIterator('ListObjects', [
            'Bucket' => $this->bucket,
            'Prefix' => $this->object,
        ]);
        $files = [];
        foreach ($request as $object) {
            $files[] = $object;
        }

        return $files;
    }

    public function create()
    {
        // Copy local directory to bucket
        if (is_dir($this->object)) {
            $this->client->uploadDirectory($this->object, $this->bucket);
        }
        // TODO: Create 1 empty directory in bucket
    }
}