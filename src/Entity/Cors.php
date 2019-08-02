<?php

namespace AntonAm\DigitalOcean\Spaces\Entity;

use AntonAm\DigitalOcean\Spaces\Manager as SpacesManager;
use Aws\S3\S3Client;

/**
 * Class Cors
 *
 * @package AntonAm\DigitalOcean\Spaces\Entity
 */
class Cors
{
    /** @var S3Client */
    private $client;
    private $bucket;

    public function __construct(SpacesManager $manager)
    {
        $this->client = $manager->getClient();
        $this->bucket = $manager->getSpace();
    }

    public function get(): array
    {
        $cors = $this->client->getBucketCors([
            'Bucket' => $this->bucket,
        ]);

        return $cors->toArray();
    }

    public function create($rules): array
    {
        if (empty($rules)) {
            $rules = [
                'AllowedMethods' => ['GET'],
                'AllowedOrigins' => ['*'],
                'ExposeHeaders'  => ['Access-Control-Allow-Origin'],
            ];
        }

        $result = $this->client->putBucketCors([
            'Bucket'            => $this->bucket,
            'CORSConfiguration' => ['CORSRules' => [$rules]]
        ]);
        return $result->toArray();
    }
}