<?php

namespace Barbare\Framework\Orm\Repository\Structure;

class Structure
{
    private $repository;
    private $dbName;
    private $primaryKey;
    private $fields = [];

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function dbName($dbName)
    {
        $this->dbName = $dbName;

        return $this;
    }

    public function primaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    public function addField($name, $callback)
    {
        $this->fields[$name] = new Field($name, $this);
        $callback($this->fields[$name]);

        return $this;
    }
}
