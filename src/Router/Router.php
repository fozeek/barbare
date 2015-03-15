<?php

namespace Barbare\Framework\Router;

class Router
{
    protected $route;
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function factory($url)
    {
        $params = [];
        $found = false;
        foreach ($this->config as $name => $value) {
            if (array_key_exists('url', $value)) {
                if (preg_match_all('#^'.preg_replace('#({[^}]+})#', '([a-zA-Z0-9]+)', $value['url']).'$#', $url, $matches)) {
                    preg_match_all('#{([^}]+)}#', $value['url'], $keys);
                    $keys = next($keys);
                    foreach ($keys as $match => $var) {
                        $params[$var] = $matches[$match+1][0];
                    }
                    $found = true;
                    break;
                }
            }
        }
        if (!$found) {
            $value = $this->config->toArray()['404'];
            $params = [];
        }

        return new Route($value['controller'], $value['action'], $params);
    }

    public function url($routeName, $params)
    {
        $url = $this->config->read($routeName)->read('url');
        
        foreach ($params as $key => $value) {
            $url = str_replace('{'.$key.'}', $value, $url);
        }

        return $url;
    }
}
