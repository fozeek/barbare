<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Schema\Attribut;
use Barbare\Framework\Orm\Sql;

class Table
{
    public $schema;
    public $name;
    public $onModel;
    public $join = false;
    public $attributs = [];
    public $timestamps = false;

    public function __construct($schema, $name, $onModel = true)
    {
        $this->onModel = $onModel;
        $this->name = $name;
        $this->schema = $schema;
    }

    public function attribut($name, $cb, $onModel = true)
    {
        $this->attributs[$name] = new Attribut($this, $name, $onModel);
        $cb($this->attributs[$name]);
        return $this;
    }

    public function attributs($attributs, $onModel = true)
    {
        foreach ($attributs as $name => $cb) {
            $this->attribut($name, $cb, $onModel);
        }
        return $this;
    }

    public function behavior($name)
    {
        $cb = $this->schema->getBehavior($name);
        $cb($this);
        return $this;
    }

    public function join($table)
    {
        $this->join = $table;
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