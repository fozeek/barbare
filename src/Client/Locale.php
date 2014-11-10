<?php

namespace Barbare\Framework\Client;

class Locale
{
    protected $currentLang = 'fr_FR';

    public function getCurrentLang() 
    {
        return $this->currentLang;
    }
}