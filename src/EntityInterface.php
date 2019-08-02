<?php

namespace AntonAm\DigitalOcean\Spaces;

interface EntityInterface
{
    public function create();

    public function download($path);

    public function delete(): bool;

    public function exist(): bool;

    public function getAcl();

    public function putAcl($acl);
}