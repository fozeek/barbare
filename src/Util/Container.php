<?php

namespace Barbare\Framework\Util;


class Container
{
    protected $factories;
    protected $storage;

    public function __construct($factories)
    {
        $this->storage = new Storage();
        $this->factories = new Storage();

        foreach ($factories as $name => $factory) {
            $this->add($name, $factory);
        }
    }

    public function get($key)
    {
        if ($class = $this->storage->read($key)) {
            return $class;
        }

        $this->storage->write(
            $key,
            $class = $this->_load($this->factories->read($key))
        );

        return $class;
    }

    public function add($key, $cb)
    {
        return $this->factories->write($key, $cb);
    }

    private function _load($cb)
    {
        if (is_string($cb) && class_exists($cb)) {
            return new $cb($this);
        }

        if (is_callable($cb)) {
            return call_user_func_array($cb, [$this]);
        }

        return $cb;
    }
}
