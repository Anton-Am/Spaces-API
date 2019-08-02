<?php

namespace Tests\Helper;

use AntonAm\DigitalOcean\Spaces\Manager as SpacesManager;
use Codeception\Module\Filesystem;

class DoSpaces extends Filesystem
{
    /** @var SpacesManager */
    private $client;
    protected $requiredFields = ['name', 'key', 'secret', 'region', 'host'];

    private function initClient(): void
    {
        if (empty($this->client)) {
            $this->client = new SpacesManager($this->config['key'], $this->config['secret'], $this->config['name'], $this->config['region'], $this->config['host']);
        }
    }

    public function getClient(): SpacesManager
    {
        $this->initClient();
        return $this->client;
    }

    public function uploadFile($file)
    {
        $this->initClient();

        $this->client->file($file)->setFileData($file)->create();
    }

    public function assertUploadedFileExists($filename, $message = '')
    {

        $this->initClient();

        $this->assertTrue($this->client->file($filename)->exist());
    }
}