<?php

namespace AntonAm\DigitalOcean\Spaces\Entity;

use AntonAm\DigitalOcean\Spaces\EntityInterface;
use AntonAm\DigitalOcean\Spaces\Manager as SpacesManager;
use Aws\S3\S3Client;

class Directory extends BucketObject
{
    public function files(): array
    {

    }
}