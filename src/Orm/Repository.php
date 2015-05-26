<?php

namespace Barbare\Framework\Orm;

use Barbare\Framework\Orm\QueryBuilder;
use Barbare\Framework\Orm\Repository\DbCollection;

class Repository
{
    private $manager;
    private $schema;

    public function __construct($manager, $schema)
    {
        $this->manager = $manager;
        $this->schema = $schema;
    }

    public function getManager()
    {
        return $this->manager;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function findAll()
    {
        $collection = [];
        $qb = QueryBuilder::create()->from($this->schema->name);
        if($this->schema->join) {
            $qb->join('LEFT', $this->schema->join, 'A.id = '.$this->schema->join.'.id');
        }
        foreach ($qb->fetchArray() as $values) {
            $collection[] = new Entity($this, $values);
        }

        return new DbCollection($collection);
    }

    public function findAllCallback($cb)
    {
        $collection = [];
        $qb = QueryBuilder::create()->from($this->schema->name);
        if($this->schema->join) {
            $qb->join('LEFT', $this->schema->join, 'A.id = '.$this->schema->join.'.id');
        }
        $cb($qb);
        foreach ($qb->fetchArray() as $values) {
            $collection[] = new Entity($this, $values);
        }

        return new DbCollection($collection);
    }

    public function findOneCallback($cb)
    {
        $collection = [];
        $qb = QueryBuilder::create()->from($this->schema->name);
        if($this->schema->join) {
            $qb->join('LEFT', $this->schema->join, 'A.id = '.$this->schema->join.'.id');
        }
        $cb($qb);
        foreach ($qb->fetchArray() as $values) {
            $collection[] = new Entity($this, $values);
        }

        return new DbCollection($collection);
    }

    public function findBy($wheres)
    {
        $collection = [];
        $qb = QueryBuilder::create()->from($this->schema->name);
        if($this->schema->join) {
            $qb->join('LEFT', $this->schema->join, 'A.id = '.$this->schema->join.'.id');
        }
        foreach ($wheres as $key => $value) {
            $qb->where($key, '=', $value);
        }
        foreach ($qb->fetchArray() as $values) {
            $collection[] = new Entity($this, $values);
        }

        return new DbCollection($collection);
    }

    public function findOneBy($wheres)
    {
        $qb = QueryBuilder::create()->from($this->schema->name);
        if($this->schema->join) {
            $qb->join('LEFT', $this->schema->join, 'A.id = '.$this->schema->join.'.id');
        }
        foreach ($wheres as $key => $value) {
            $qb->where($key, '=', $value);
        }

        return new Entity($this, $qb->fetchArray()[0]);
    }
}
