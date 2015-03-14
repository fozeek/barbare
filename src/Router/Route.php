<?php

namespace Barbare\Framework\Router;

class Route
{
    protected $controller;
    protected $action;
    protected $params;

    public function __construct($controller, $action, $params)
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->params = $params;
    }

    public function toArray($withParams = true)
    {
        $route = [
            'controller' => $this->controller,
            'action' => $this->action,
        ];

        if ($withParams) {
            return array_merge(
                $route,
                ['params' => $this->params]
            );
        }

        return $route;
    }

    public function getController($long = true)
    {
        if ($long) {
            return $this->controller;
        } else {
            $exp = explode('\\', $this->controller);

            return end($exp);
        }
    }

    public function getAction($long = true)
    {
        if ($long) {
            return $this->action;
        } else {
            $exp = explode('\\', $this->action);

            return end($exp);
        }
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($key)
    {
        return $this->params[$key];
    }
}
