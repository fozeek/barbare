<?php

namespace Barbare\Framework\Orm\Repository\Structure\Behavior;

class I18n extends Behavior
{
    protected $table;
    protected $langs;
    protected $defaultValue;
    protected $defaultLang;
    protected $currentLang;

    public function table($table)
    {
        $this->table = $table;
    }

    public function langs($langs)
    {
        $this->langs = $langs;
    }

    public function defaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    public function defaultLang($defaultLang)
    {
        $this->defaultLang = $defaultLang;
    }

    public function currentLang($currentLang)
    {
        $this->currentLang = $currentLang;
    }
}