<?php

namespace Barbare\Framework\Router;

class Route
{
    protected $callback;
    protected $url;
    protected $name;
    protected $childs = [];
    protected $parent;
    protected $params = [];

    public function __construct($url, $callback, $name = null, $extends = false, $parent = false)
    {
        $this->callback = $callback;
        $this->url = $url;
        $this->name = $name;
        $this->parent = $parent;
        if($extends) {
            $extends($this);
        }
    }

    public function add($url, $callback, $name = null, $extends = false)
    {
        $this->childs[] = new Route($url, $callback, $name, $extends, $this);
    }

    public function matchName($name)
    {
        if($this->getFullName() == $name) {
            return $this;
        }
        foreach ($this->childs as $route) {
            if($matched = $route->matchName($name)) {
                return $matched;
            }
        }
        return false;
    }

    public function getFullName()
    {
        $name = $this->name;
        if($this->parent) {
            $name = $this->parent->getFullName().'/'.$name;
        }
        return $name;
    }

    public function match($url)
    {
        if (preg_match_all('#^'.preg_replace('#({[^}]+})#', '([a-zA-Z0-9\-\_]+)', $this->getFullUrl()).'$#', $url, $matches)) {
            // Retrieve paramaters
            preg_match_all('#{([^}]+)}#', $this->getFullUrl(), $keys);
            $keys = next($keys);
            foreach ($keys as $match => $var) {
                $this->params[$var] = $matches[$match+1][0];
            }
            return $this;
        }
        foreach ($this->childs as $route) {
            if($matched = $route->match($url)) {
                return $matched;
            }
        }
        return false;
    }

    public function getFullUrl($params = [])
    {
        $url = $this->url;
        if($this->parent) {
            $url = $this->parent->getFullUrl($params).$url;
        }
        foreach ($params as $key => $value) {
            $url = str_replace('{'.$key.'}', $value, $url);
        }
        return $url;
    }

    public function getCallback($long = true)
    {
        return $this->callback;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($key)
    {
        if(!isset($this->params[$key])) {
            return false;
        }
        return $this->params[$key];
    }

    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function setParams($params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }
}
