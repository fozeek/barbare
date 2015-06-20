<?php

namespace Barbare\Framework\Orm;

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

    public function create($values)
    {
        $entity = $this->fillEntity($values);
        // Do special stuff (pre-save)
        $errors = [];
        foreach ($this->schema->attributs as $attribut) {
            foreach ($attribut->events['create'] as $cb) {
                $cb = $cb->bindTo($entity);
                $values[$attribut->name] = $cb();
            }
        }
        if(!$entity->check()) {
            return false;
        }

        $id = QueryBuilder::create()->insert($this->schema->name)->columnsValues($values)->execute();

        return $this->findOneBy(['id' => $id]);
    }

    public function getManager()
    {
        return $this->manager;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function save($entity)
    {
        // Format data
        $values = [];
        $data = $entity->toArray();
        foreach ($this->schema->attributs as $attribut) {
            if (isset($data[$attribut->name]) && !is_array($data[$attribut->name])) {
                $values[$attribut->name] = $data[$attribut->name];
            }
        }

        // Do special stuff (pre-save)
        foreach ($this->schema->attributs as $attribut) {
            foreach ($attribut->events['save'] as $cb) {
                $cb = $cb->bindTo($entity);
                $values[$attribut->name] = $cb();
            }
        }

        return QueryBuilder::create()->update($this->schema->name)->where('id', '=', $entity->get('id'))->columnsValues($values)->execute();
    }

    public function findAll()
    {
        $collection = [];
        $qb = QueryBuilder::create()->from($this->schema->name);
        if ($this->schema->join) {
            $qb->join('LEFT', $this->schema->join, 'A.id = '.$this->schema->join.'.id');
        }
        foreach ($qb->fetchArray() as $values) {
            $collection[] = $this->fillEntity($values);
        }

        return new DbCollection($collection);
    }

    public function findAllCallback($cb)
    {
        $collection = [];
        $qb = QueryBuilder::create()->from($this->schema->name);
        if ($this->schema->join) {
            $qb->join('LEFT', $this->schema->join, 'A.id = '.$this->schema->join.'.id');
        }
        $cb($qb);
        foreach ($qb->fetchArray() as $values) {
            $collection[] = $this->fillEntity($values);
        }

        return new DbCollection($collection);
    }

    public function findOneCallback($cb)
    {
        $collection = [];
        $qb = QueryBuilder::create()->from($this->schema->name);
        if ($this->schema->join) {
            $qb->join('LEFT', $this->schema->join, 'A.id = '.$this->schema->join.'.id');
        }
        $qb->limit(0, 1);
        $cb($qb);
        $data = $qb->fetchArray();
        if (count($data) < 1) {
            return false;
        }

        return $this->fillEntity($data[0]);
    }

    public function findBy($wheres)
    {
        $collection = [];
        $qb = QueryBuilder::create()->from($this->schema->name);
        if ($this->schema->join) {
            $qb->join('LEFT', $this->schema->join, 'A.id = '.$this->schema->join.'.id');
        }
        foreach ($wheres as $key => $value) {
            $qb->where($key, '=', $value);
        }
        foreach ($qb->fetchArray() as $values) {
            $collection[] = $this->fillEntity($values);
        }

        return new DbCollection($collection);
    }

    public function findOneBy($wheres)
    {
        $qb = QueryBuilder::create()->from($this->schema->name);
        if ($this->schema->join) {
            $qb->join('LEFT', $this->schema->join, 'A.id = '.$this->schema->join.'.id');
        }
        foreach ($wheres as $key => $value) {
            $qb->where('A.'.$key, '=', $value);
        }
        $qb->limit(0, 1);
        $data = $qb->fetchArray();
        if (count($data) < 1) {
            return false;
        }

        return $this->fillEntity($data[0]);
    }

    private function fillEntity($data)
    {
        $entityClassName = $this->schema->getEntityClassName() ?: 'Barbare\Framework\Orm\Entity';

        return new $entityClassName($this, $data);
    }
}
