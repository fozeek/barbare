<?php

namespace Barbare\Framework\Module;

use Barbare\Framework\Util\Storage;

class ModuleManager
{
    protected $modules;
    protected $application;

    public function __construct($application, $config)
    {
        $this->application = $application;
        $this->_loadModules($config);
    }

    protected function _loadModules($config, $args = array())
    {
        $this->modules = new Storage();
        foreach ($config as $key => $value) {
            $value .= '\Module';
            $this->modules->write(
                $key,
                (is_callable($value)) ? call_user_func_array($value, [$this->application, $this, $args]) : new $value($this->application, $args)
            );

            // Initialisation du module
            $this->modules->last()->onBoostrap();

            $this->application->getServiceManager()
                ->loadFromModule($this->modules->last());
            // $this->application->getEventManager()
            //	->loadFromModule($this->modules->last());
        }
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
