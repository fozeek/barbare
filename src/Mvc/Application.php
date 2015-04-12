<?php

namespace Barbare\Framework\Mvc;

use Barbare\Framework\Util\Storage;
use Barbare\Framework\Service\ServiceManager;
use Barbare\Framework\Module\ModuleManager;
use Barbare\Framework\Event\EventManager;

class Application
{
    protected $config;
    protected $moduleManager;
    protected $serviceManager;
    protected $eventManager;

    public function __construct($config)
    {
        $this->config = new Storage($config);
        $this->eventManager = new EventManager();
        $this->serviceManager = new ServiceManager($this, $this->config->read('services'));
        $this->serviceManager->set('application', $this);
        $this->moduleManager = new ModuleManager($this, $this->config->read('modules'));
        $this->eventManager
            ->attach('dispatch', [new Dispatcher($this), 'dispatch'])
            ->trigger('dispatch', $this->serviceManager->get('request')->initDispatchEvent());
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

    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}
