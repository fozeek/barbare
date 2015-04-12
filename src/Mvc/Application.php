<?php

namespace Barbare\Framework\Mvc;

use Barbare\Framework\Util\Storage;
use Barbare\Framework\Util\Container;
use Barbare\Framework\Module\ModuleManager;
use Barbare\Framework\Event\EventManager;

class Application
{
    protected $config;
    protected $moduleManager;
    protected $services;
    protected $eventManager;

    public function __construct($config)
    {
        $this->config = new Storage($config);
        $this->eventManager = new EventManager();
        $this->services = new Container($this->config->read('services'));
        $this->services->add('application', $this);
        $this->moduleManager = new ModuleManager($this, $this->config->read('modules'));
        $this->eventManager
            ->attach('dispatch', [new Dispatcher($this), 'dispatch'])
            ->trigger('dispatch', $this->services->get('request')->initDispatchEvent());
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
