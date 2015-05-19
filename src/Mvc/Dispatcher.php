<?php

namespace Barbare\Framework\Mvc;

use Barbare\Framework\Event\Event;

class Dispatcher
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function dispatch(Event $event)
    {
        $controllerName = $event->getData()->read('route')->getController();
        $controller = new $controllerName($this->app);
        $this->app->addService('controller', $controller);
        return call_user_func_array([
            $controller,
            $event->getData()->read('route')->getAction(),
        ], $event->getData()->read('route')->getParams());
    }
}
