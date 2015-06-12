<?php

namespace Barbare\Framework\Http;

use Barbare\Framework\Event\Event;
use Barbare\Framework\Router\Route;
use Barbare\Framework\Client\Client;

class Request
{
    protected $route;
    protected $container;
    protected $client;

    public function __construct($container)
    {
        $this->container = $container;
        $this->client = new Client();
    }

    public function getBaseUrl()
    {
        return $_SERVER['REDIRECT_URL'];
    }

    public function getData($key = false)
    {
        $data = array_merge(
            $_REQUEST,
            $_FILES
        );
        if ($key) {
            $data = $data[$key];
        }

        if(isset($data['PHPSESSID'])) {
            unset($data['PHPSESSID']);
        }

        return $data;
    }

    public function is($method)
    {
        return strtolower($method) == strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function initDispatchEvent()
    {
        $this->route = $this->container->get('router')->match($this->getBaseUrl());
        return new Event([
            'route' => $this->route,
        ]);
    }

    public function getClient()
    {
        return $this->client;
    }
}
