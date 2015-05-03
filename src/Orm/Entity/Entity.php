<?php

namespace Barbare\Framework\Orm\Entity;

use Barbare\Framework\Orm\QueryBuilder;
use Barbare\Framework\Orm\Repository\DbCollection;

class Entity
{
    private $repository;
    protected $attributs;
    protected $associations;

    public function __construct($repository, $attributs)
    {
        $this->repository = $repository;
        $this->attributs = $attributs;
    }

    public function get($attribut)
    {
        if (isset($this->attributs[$attribut])) {
            return $this->attributs[$attribut];
        } elseif ($this->associations[$attribut]) {
            return $this->_fetchAssoc($this->associations[$attribut]);
        }

        return false;
    }

    public function set($attribut, $value)
    {
        $this->attributs[$attribut] = $value;

        return $this;
    }

    public function toArray()
    {
        return $this->attributs;
    }

    private function _fetchAssoc($assoc)
    {
        $foreignRepo = $this->repository->getManager()->get($assoc['reference']);
        if($assoc['type'] == 'manyToMany') {
            $data = QueryBuilder::create()->from([$this->repository->getTableName().'_'.$assoc['reference'], $foreignRepo->getTableName()])
                ->where($this->get('id'), '=', 'A.'.$this->repository->getTableName().'_id', false)
                ->andWhere('A.'.$assoc['reference'].'_id', '=', 'B.id', false)
                ->fetchArray();
            foreach ($data as $key => $value) {
                unset($data[$key][$this->repository->getTableName().'_id']);
                unset($data[$key][$assoc['reference'].'_id']);
            }
            $collection = [];
            $entityClassName = $foreignRepo->getEntityClassName();
            foreach ($data as $values) {
                $collection[] = new $entityClassName($foreignRepo, $this->repository->afterFind($values));
            }
            return new DbCollection($collection);
        } elseif ($assoc['type'] == 'oneToMany') {
            return $foreignRepo->findBy($this->repository->getTableName().'_id', intval($this->get('id')));
        }
    }
}
