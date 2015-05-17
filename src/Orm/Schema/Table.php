<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Schema\Attribut;
use Barbare\Framework\Orm\Sql;

class Table
{
    public $name;
    public $attributs = [];
    public $timestamps = false;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function attribut($name, $cb)
    {
        $this->attributs[$name] = new Attribut($name);
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
}


/*
ADD UNIQUE(`slug`);
*/  