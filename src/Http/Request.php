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
        if ($pos = strpos($_SERVER['REQUEST_URI'], '?')) {
            return substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
        }

        return $_SERVER['REQUEST_URI'];
    }

    public function getParam($key = false)
    {
        if($key) {
            return $_GET[$key];
        }
        return $_GET;
    }

    public function getData($key = false)
    {
        $data = array_merge(
            $_REQUEST,
            $_FILES
        );
        if ($key) {
            if (!isset($data[$key])) {
                return false;
            }
            $data = $data[$key];
        }

        if (isset($data['PHPSESSID'])) {
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
