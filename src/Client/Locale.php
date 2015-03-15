<?php

namespace Barbare\Framework\Client;

class Locale
{
    public function getCurrentLang()
    {
        return \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }
}
