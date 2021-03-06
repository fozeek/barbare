<?php

namespace Barbare\Framework\Orm\Repository;

use Iterator;

class DbCollection implements Iterator
{
    private $data = [];
    private $repository;
    private $position = 0;

    public function __construct($collection = [])
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

    public function add($entity)
    {
        $this->data[] = $entity;
    }

    public function last()
    {
        return end($this->data);
    }

    public function order($cb)
    {
        usort($this->data, $cb);
        return $this;
    }

    public function orderBy($attribut, $order = 'ASC')
    {
        return $this->order(function($i, $f) use ($attribut, $order) {
            $iValue = is_string($attribut) ? $i->get($attribut) : $attribut($i);
            $fValue = is_string($attribut) ? $f->get($attribut) : $attribut($f);
            if($iValue == $fValue) return 0;
            else {
                if($order == 'ASC') {
                    return $iValue > $fValue ? 1: -1;
                } else {
                    return $iValue < $fValue ? 1: -1;
                }
            }
        });
    }

    public function count()
    {
        return count($this->data);
    }

    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return false;
    }

    public function filter($cb)
    {
        $res = [];
        foreach ($this->data as $key => $entity) {
            if ($cb($entity)) {
                $res[] = $entity;
            }
        }

        return new Self($res);
    }

    public function slice($offset, $length = 20)
    {
        $res = array_slice($this->data, $offset, $length);

        return new Self($res);
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
        return get_class($entity1) == get_class($entity2) && $entity1->get('id') == $entity2->get('id');
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
