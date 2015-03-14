<?php

namespace Barbare\Framework\Service;

use Barbare\Framework\Util\Storage;

class ServiceManager
{
    protected $services;
    protected $application;

    public function __construct($application, $config)
    {
        $this->services = new Storage();
        $this->application = $application;
        $this->_loadServices($config);
    }

    protected function _loadServices($config)
    {
        foreach ($config as $key => $value) {
            $this->_load($key, $value);
        }
    }

    public function loadFromModule($module)
    {
        foreach ($module->getServiceConfig() as $key => $value) {
            $this->_load($key, $value);
        }
    }

    protected function _load($key, $service)
    {
        $this->services->write(
            $key,
            (is_callable($service)) ?
                call_user_func_array($service, [$this->application, $this]) :
                (
                    (is_object($service)) ?
                        $service :
                        new $service($this->application)
                )
        );
    }

    public function get($service)
    {
        return $this->services->read($service);
    }

    public function set($name, $service)
    {
        $this->services->write($name, $service);
    }
}
