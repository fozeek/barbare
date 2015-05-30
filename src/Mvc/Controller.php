<?php

namespace Barbare\Framework\Mvc;

use Barbare\Framework\Util\Container;

class Controller
{
    protected $components;
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->components = new Container($this->application->getConfig()->read('components'));
        $this->components->add('application', $application);
        $this->components->add('controller', $this);

        $this->init();
    }

    public function get($attribut)
    {
        return $this->components->get(strtolower($attribut));
    }

    public function redirect($url, $params = [])
    {
        header('Location:'.$this->get('application')->getService('router')->url($url, $params));
        die;
    }

    public function init()
    {
    }

    public function dispatch($routeName, $params = [])
    {
        $route = $this->application->getService('router')->findRoute($routeName);
        call_user_func_array([new Dispatcher($this->application), 'call'], ['route' => $route, 'params' => $params]);
        die;
    }
}
