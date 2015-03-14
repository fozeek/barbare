<?php

namespace Barbare\Framework\Acl;

use Barbare\Framework\Acl\Config\Config;

class Acl
{
    protected $config;

    public function __construct()
    {
        $this->config = new Config();
    }

    public function getNamespace($namespace)
    {
        return $this->config->getNamespace($namespace);
    }
}
