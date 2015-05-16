<?php

namespace Barbare\Framework\Mvc;

use Barbare\Framework\Util\Storage;
use Barbare\Framework\Util\Container;
use Barbare\Framework\Event\EventManager;

class Console
{
    protected $config;
    protected $services;
    protected $eventManager;

    public function __construct($config)
    {
        $this->config = new Storage($config);
        $this->eventManager = new EventManager();
        $this->services = new Container($this->config->read('services'));
        $this->services->add('application', $this);
        $cb = $this->config->read('routes.'.$_SERVER['argv'][1]);
        call_user_func_array($cb->bindTo($this), array_slice($_SERVER['argv'], 2));
    }

    public static function run($config)
    {
        return new self($config);
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }

    public function getModuleManager()
    {
        return $this->moduleManager;
    }

    public function getService($service)
    {
        return $this->services->get($service);
    }
}
