<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Schema\Attribut;
use Barbare\Framework\Orm\Sql;

class Table
{
    public $schema;
    public $name;
    public $join = false;
    public $onModel;
    public $ephemeral = false;
    public $attributs = [];
    public $entityClassName = false;
    public $repositoryClassName = false;

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

    public function union($tables, $attributs)
    {
        $this->ephemeral = true;
        $this->union = $tables;
        foreach ($attributs as $attribut) {
            $this->attribut($attribut, function(){});
        }
        return $this;
    }

    public function join($table)
    {
        $this->join = $table;
        $this->schema->get($table)->attribut('_join_table_name', function($attribut) {
            $attribut->type('varchar', 250);
            $attribut->null();
        }, false);
        $this->attribut('id', function($attribut) {
            $attribut->type('int', 11);
            $attribut->unique();
        });
        // $this->schema->get($table)->attribut('_join_table_id', function($attribut) {
        //     $attribut->type('int', 11);
        //     $attribut->null();
        // }, false);
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

    public function entity($entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    public function repository($repositoryClassName)
    {
        $this->repositoryClassName = $repositoryClassName;
    }
}


/*
ADD UNIQUE(`slug`);
*/  