<?php

namespace Barbare\Framework\Util;

use Exception;

class Prototype
{
    private $object;
    private $methods;

    public function __construct($object)
    {
        $this->object = $object;
    }

    public function addMethod($name, $method)
    {
        $this->methods[$name] = $method;
    }

    public function __call($method, $args)
    {
        if (array_key_exists($method, $this->methods)) {
            throw new Exception("Error Processing Method ".$method." for ".get_class($this->object)." object.", 1);

            return false;
        }

        return call_user_func_array($this->method[$method]->bind($this->object), $args);
    }
}
