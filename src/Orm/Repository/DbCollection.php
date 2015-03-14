<?php

namespace Barbare\Framework\Orm\Repository;

use Iterator;

class DbCollection implements Iterator
{
    private $data = [];
    private $repository;
    private $position = 0;

    public function __construct($collection)
    {
        $this->data = $collection;
    }

    public function each($cb)
    {
        foreach ($this->data as $key => $entity) {
            $this->data[$key] = $cb($entity);
        }

        return $this;
    }

    public function filter($cb)
    {
        $res = [];
        foreach ($this->data as $key => $entity) {
            if ($cb($entity)) {
                $res[] = $entity;
            }
        }
        $this->data = $res;

        return $this;
    }

    public function slice($offset, $length = 20)
    {
        $this->data = array_slice($this->data, $offset, $length);

        return $this;
    }

    public function contains($entity)
    {
        foreach ($this->data as $reference) {
            if ($this->compareEntities($entity, $reference)) {
                return true;
            }
        }

        return false;
    }

    private function compareEntities($entity1, $entity2)
    {
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->data[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->data[$this->position]);
    }
}
