<?php

namespace Barbare\Framework\Client;

class Client
{
    protected $locale;

    public function __construct()
    {
        $this->locale = new Locale();
    }

    public function getLocale()
    {
        return $this->locale;
    }
}
