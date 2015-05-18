<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Schema\Attribut;
use Barbare\Framework\Orm\Sql;

class Table
{
    public $name;
    public $onModel;
    public $attributs = [];
    public $timestamps = false;

    public function __construct($name, $onModel = true)
    {
        $this->onModel = $onModel;
        $this->name = $name;
    }

    public function attribut($name, $cb, $onModel = true)
    {
        $this->attributs[$name] = new Attribut($this, $name, $onModel);
        $cb($this->attributs[$name]);
    }

    public function timestamps()
    {
        $this->timestamps = true;
    }

    public function getSql()
    {
        return Sql::table($this);
    }

    public function get($attribut)
    {
        return $this->attributs[$attribut];
    }

    public function mapping($attributName, $type, $cb)
    {
        $this->attribut($attributName, function($attribut) use ($type, $cb) {
            $attribut->mapping($type, $cb);
        });
    }
}


/*
ADD UNIQUE(`slug`);
*/  