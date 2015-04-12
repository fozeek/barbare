<?php

namespace Barbare\Framework\Service;

use Barbare\Framework\Util\Container;

class ServiceManager
{
    protected $services;
    protected $application;

    public function __construct($application, $config)
    {
        $this->services = new Container($config);
        $this->services->add('application', $application);
        $this->application = $application;
    }

    public function get($service)
    {
        return $this->services->get($service);
    }

    public function set($name, $service)
    {
        $this->services->add($name, $service);
    }
}
