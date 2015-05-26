<?php

namespace Barbare\Framework\Mvc;

use Barbare\Framework\Event\Event;
use Closure;
use Barbare\Framework\Mvc\Controller;

class Dispatcher
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function dispatch(Event $event)
    {
        if(is_callable($event->getData()->read('route')->getCallback())) {
            $this->call($event->getData()->read('route'));
        } else {
            $this->call($this->app->getService('router')->findRoute('error'), ['code' => 404, 'message' => 'The route is uncallable !']);
        }
    }

    public function call($route, $params = []) 
    {
        $call = $route->getCallback();
        if($call instanceof Closure) {
            $controller = new Controller($this->app);
            $this->app->addService('controller', $controller);
            $call = $call->bindTo($controller);
            return call_user_func_array($call, array_merge($route->getParams(), $params));
        } else {
            $controllerName = $call[0];
            $controller = new $controllerName($this->app);
            $this->app->addService('controller', $controller);
            return call_user_func_array([
                $controller,
                $call[1]
            ], array_merge($route->getParams(), $params));
        }
    }
}
