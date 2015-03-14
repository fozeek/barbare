<?php

namespace Barbare\Framework\View\Helper;

class Translator
{
    private $lang = 'en_EN';
    private $path;
    private $data = [];

    public function __construct($path)
    {
        $this->setPath($path);
        $this->setData();
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
        $this->setData();
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    private function setData()
    {
        $this->data = require $this->path.$this->lang.'.php';
    }

    public function __invoke($key)
    {
        // TODO sprintf() with unlimited params

        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } else {
            return $key;
        }
    }
}
