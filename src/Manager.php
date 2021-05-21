<?php

namespace AntonAm\DigitalOcean\Spaces;

use AntonAm\DigitalOcean\Spaces\Entity\Bucket;
use AntonAm\DigitalOcean\Spaces\Entity\Directory;
use AntonAm\DigitalOcean\Spaces\Entity\File;
use Aws\S3\S3Client;

/**
 * Class Manager
 *
 * @package AntonAm\DigitalOcean\Spaces
 */
class Manager
{
    private $client;
    private $accessKey;
    private $secretKey;
    private $space;
    private $host;
    private $region;

    public function __construct($accessKey, $secretKey, $spaceName = '', $region = 'nyc3', $host = 'digitaloceanspaces.com')
    {
        if (!class_exists(S3Client::class)) {
            throw new SpacesException('There is no AWS client. Please update composer.');
        }

        $this->setSpace($accessKey, $secretKey, $spaceName, $region, $host);
    }

    public function setSpace($accessKey = null, $secretKey = null, $spaceName = null, $region = 'fra1', $host = 'digitaloceanspaces.com'): void
    {
        if (empty($accessKey)) {
            $accessKey = $this->accessKey;
        }
        if (empty($secretKey)) {
            $secretKey = $this->secretKey;
        }
        if (empty($region)) {
            $region = $this->region;
        }
        if (empty($host)) {
            $host = $this->host;
        }

        if (!empty($spaceName)) {
            $endpoint = 'https://' . $spaceName . '.' . $region . '.' . $host;
        } else {
            $endpoint = 'https://' . $region . '.' . $host;
        }

        $this->client = new S3Client([
            'region'            => $region,
            'version'           => 'latest',
            'endpoint'          => $endpoint,
            'credentials'       => [
                'key'    => $accessKey,
                'secret' => $secretKey,
            ],
            'bucket_endpoint'   => true,
            //'signature_version' => 'v4-unsigned-body'
        ]);

        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->space = $spaceName;
        $this->region = $region;
        $this->host = $host;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getSpace()
    {
        return $this->space;
    }

    public function bucket(): Bucket
    {
        return new Bucket($this);
    }

    public function directory($directory): Directory
    {
        return new Directory($this, ltrim($directory, '/'));
    }

    public function file($file): File
    {
        return new File($this, trim($file, '/'));
    }
}