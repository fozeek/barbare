<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Sql;

class Table
{
    public $schema;
    public $name;
    public $join = false;
    private $tableName = false;
    public $onModel;
    public $ephemeral = false;
    public $attributs = [];
    private $entityClassName = false;
    private $repositoryClassName = false;

    public function __construct($schema, $name, $onModel = true)
    {
        $this->onModel = $onModel;
        $this->name = $name;
        $this->schema = $schema;
    }

    public function getTableName()
    {
        if($tableName) {
            return $this->tableName;
        }
        return $name;
    }

    public function tableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function getEntityClassName()
    {
        if($this->entityClassName) {
            return $this->entityClassName;
        } elseif ($this->join) {
            if($this->schema->get($this->join)->getEntityClassName()) {
                return $this->schema->get($this->join)->getEntityClassName();
            }
        }
        return false;
    }

    public function getRepositoryClassName()
    {
        if($this->repositoryClassName) {
            return $this->repositoryClassName;
        } elseif ($this->join) {
            if($this->schema->get($this->join)->getRepositoryClassName()) {
                return $this->schema->get($this->join)->getRepositoryClassName();
            }
        }
        return false;
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
            $this->attribut($attribut, function () {});
        }

        return $this;
    }

    public function join($table)
    {
        $this->join = $table;
        $this->schema->get($table)->attribut('_join_table_name', function ($attribut) {
            $attribut->type('varchar', 250);
            $attribut->null();
        }, false);
        $this->attribut('id', function ($attribut) {
            $attribut->type('int', 11);
            $attribut->unique();
        });
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
        if(isset($this->attributs[$attribut])) { // self attribut
            return $this->attributs[$attribut];
        } 
        if($this->join) { // parent attribut
            $joinModel = $this->schema->get($this->join);
            if($joinModel->get($attribut) !== false) {
                return $joinModel->get($attribut);
            }
        }
        return false;
    }

    public function mapping($attributName, $type, $cb)
    {
        $this->attribut($attributName, function ($attribut) use ($type, $cb) {
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
