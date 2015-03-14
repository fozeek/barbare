<?php

namespace Barbare\Framework\Orm\Entity;

use Barbare\Framework\Orm\QueryBuilder;
use Barbare\Framework\Orm\Repository\DBCollection;

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

    // public function __get($attribut)
    // {
    //     return $this->attributs[$attribut];
    // }

    public function get($attribut)
    {
        if (isset($this->attributs[$attribut])) {
            return $this->attributs[$attribut];
        } elseif ($this->associations[$attribut]) {
            return $this->_fetchAssoc($this->associations[$attribut]);
        }

        return false;
    }

    private function _fetchAssoc($assoc)
    {
        $foreignRepo = $this->repository->getManager()->get($assoc['reference']);
        $data = QueryBuilder::create()->from([$this->repository->getTableName().'_'.$assoc['reference'], $foreignRepo->getTableName()])
            ->where($this->get('id'), '=', 'A.'.$this->repository->getTableName().'_id', false)
            ->andWhere('A.'.$assoc['reference'].'_id', '=', 'B.id', false)
            ->fetchArray();
        //$query = 'SELECT * FROM '.$this->repository->getTableName().'_'.$assoc['reference'].' A, '.$foreignRepo->getTableName().' B WHERE '.$this->get('id').' = A.'.$this->repository->getTableName().'_id AND A.'.$assoc['reference'].'_id = B.id';
        //$data = QueryBuilder::create()->query($query);
        foreach ($data as $key => $value) {
            unset($data[$key][$this->repository->getTableName().'_id']);
            unset($data[$key][$assoc['reference'].'_id']);
        }
        $collection = [];
        $entityClassName = $foreignRepo->getEntityClassName();
        foreach ($data as $values) {
            $collection[] = new $entityClassName($foreignRepo, $this->repository->afterFind($values));
        }

        return new DBCollection($collection);
    }
}
