<?php

namespace Barbare\Framework\Http;

use Barbare\Framework\Event\Event;
use Barbare\Framework\Router\Route;
use Barbare\Framework\Client\Client;

class Request
{
    protected $route;
    protected $app;
    protected $client;

    public function __construct($app)
    {
        $this->app = $app;
        $this->client = new Client();
    }

    public function getBaseUrl()
    {
        return $_SERVER['REQUEST_URI'];
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
        $this->route = $this->app->getServiceManager()->get('router')->factory($this->getBaseUrl());

        return new Event([
            'route' => $this->route,
        ]);
    }

    public function getClient()
    {
        return $this->client;
    }
}
