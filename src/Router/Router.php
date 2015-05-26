<?php

namespace Barbare\Framework\Router;

class Router
{
    protected $routes = [];
    protected $config;
    protected $cb;

    public function __construct($cb)
    {
        $cb($this);
    }

    public function add($url, $callback, $name = null, $extends = false)
    {
        $this->routes[] = new Route($url, $callback, $name, $extends);
    }

    public function match($url)
    {
        foreach ($this->routes as $route) {
            if($found = $route->match($url)) {
                return $found;
            }
        }
        die('no route found'); // TODO 404
    }

    public function url($routeName, $params = array())
    {
        $found = $this->findRoute($routeName);
        if(!$found) {
            return false;
        }
        $url = $found->getFullUrl();
        foreach ($params as $key => $value) {
            $url = str_replace('{'.$key.'}', $value, $url);
        }
        return $url;
    }

    public function findRoute($routeName)
    {
        foreach ($this->routes as $route) {
            if($matched = $route->matchName($routeName)) {
                return $matched;
            }
        }
        return false;
    }
}
