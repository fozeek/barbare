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

        $this->init();
    }

    public function get($attribut)
    {
        return $this->components->get(strtolower($attribut));
    }

    public function redirect($url)
    {
        header('Location:'.$url);
        die;
    }

    public function init()
    {
    }

    public function dispatch($route, $params = [])
    {
        call_user_func_array([new Dispatcher($this->application), 'call'], $params);
    }
}
