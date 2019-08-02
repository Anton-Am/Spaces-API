<?php

namespace AntonAm\DigitalOcean\Spaces\Entity;

use AntonAm\DigitalOcean\Spaces\Manager as SpacesManager;

class Cors
{
    private $client;

    public function __construct(SpacesManager $manager, $directory)
    {
        $this->client = $manager->getClient();
    }

    public function get()
    {

    }

    public function put($rules)
    {

    }
}