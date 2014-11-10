<?php

namespace Barbare\Framework\View\Helper;

class Translator
{

    private $data = [];

    public function __construct($file)
    {
        $this->data = include $file;
    } 

    public function __invoke($key)
    {
        // TODO sprintf() with unlimited params

        if(array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } else {
            return $key;
        }
    }

}