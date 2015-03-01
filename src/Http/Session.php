<?php

namespace Barbare\Framework\Http;

class Session
{

    private $data;

    public function __construct()
    {
        $this->data = $_SESSION;
    }

    public function get($key)
    {
        return $this->data[$key];
    }

    public function add($key, $value)
    {
        $this->data[$key] = $value;
        $_SESSION[$key] = $value;
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function remove($key)
    {
        unset($this->data[$key]);
        unset($_SESSION[$key]);
    }

}