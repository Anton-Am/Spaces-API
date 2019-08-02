<?php

namespace AntonAm\DigitalOcean\Spaces\Entity;

use AntonAm\DigitalOcean\Spaces\EntityInterface;
use AntonAm\DigitalOcean\Spaces\Manager as SpacesManager;
use Aws\S3\S3Client;

/**
 * Class Bucket
 *
 * @package AntonAm\DigitalOcean\Spaces\Entity
 */
class Bucket implements EntityInterface
{
    /** @var S3Client */
    private $client;
    private $manager;
    private $bucket;

    public function __construct(SpacesManager $manager)
    {
        $this->manager = $manager;
        $this->client = $manager->getClient();
        $this->bucket = $manager->getSpace();
    }

    public function new($spaceName): self
    {
        $this->manager->setSpace(null, null, $spaceName);

        return $this;
    }

    public function create(): array
    {
        $success = $this->client->createBucket(['Bucket' => $this->bucket]);
        $this->client->waitUntil('BucketExists', ['Bucket' => $this->bucket]);

        return $success->toArray();
    }

    public function download($path): bool
    {
        return !empty($this->client->downloadBucket($this->client, $this->bucket));
    }

    public function delete(): bool
    {

        $this->client->deleteBucket(['Bucket' => $this->bucket]);
        $this->client->waitUntil('BucketNotExists', ['Bucket' => $this->bucket]);

        return true;
    }

    public function exist(): bool
    {
        return $this->client->doesBucketExist($this->bucket);
    }

    public function getAcl(): array
    {
        $result = $this->client->getBucketAcl(['Bucket' => $this->bucket]);
        return $result->toArray();
    }

    public function putAcl($acl): array
    {
        $result = $this->client->putBucketAcl($acl);
        return $result->toArray();
    }


    public function files(): array
    {
        $request = $this->client->getIterator('ListObjects', [
            'Bucket' => $this->bucket,
            'Prefix' => '',
        ]);
        $files = [];
        foreach ($request as $object) {
            $files[] = $object;
        }

        return $files;
    }

}