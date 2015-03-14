<?php

namespace Barbare\Framework\Mvc;

use Barbare\Framework\Util\Storage;

abstract class Controller
{
    protected $components;
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->components = new Storage();

        $this->init();
    }

    protected function _loadComponent($key)
    {
        $key = strtolower($key);
        if ($component = $this->components->read($key)) {
            return $component;
        }
        $config = $this->application->getConfig()->read('components')->read($key);
        $args = [
            'application' => $this->application,
            'controller' => $this,
        ];
        if (is_callable($config)) {
            $component = call_user_func_array($config, [$this->application, $this]);
        } else {
            if (is_object($config)) {
                $component = $config;
            } else {
                $component = new $config($this->application, $this);
            }
        }
        $this->components->write($key, $component);

        return $component;
    }

    public function get($attribut)
    {
        return $this->_loadComponent($attribut);
    }

    public function init()
    {
    }

    public function redirect($url)
    {
        header('Location:'.$url);
        die;
    }
}
