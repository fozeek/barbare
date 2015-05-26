<?php

namespace Barbare\Framework\Orm;

use Barbare\Framework\Orm\QueryBuilder;
use Barbare\Framework\Orm\Repository\DbCollection;

class Entity
{
    private $repository;
    private $schema;
    private $attributs = [];

    public function __construct($repository, $attributs)
    {
        $this->repository = $repository;
        $this->schema = $repository->getSchema();
        $this->attributs = $attributs;
    }

    public function get($attribut)
    {
        if (isset($this->attributs[$attribut])) {
            return $this->attributs[$attribut];
        } elseif ($this->schema->get($attribut)->mapping) {
            return $this->attributs[$attribut] = $this->_build($attribut);
        }

        return false;
    }

    public function getInheritance() {
        return $this->get('_join_table_name');
    }

    private function _build($attribut)
    {
        $schemAttribut = $this->schema->attributs->get($attribut);
        $foreignRepo = $this->repository->getManager()->get($schemAttribut->mapping->table);
        if($assoc['type'] == 'manyToMany') {
            return $foreignRepo->findAllCallback(function($qb) {
                $qb->from($this->schema->name.'_'.$foreignRepo->name)
                    ->where($schemAttribut->mapping->associatedKey, '=', $schemAttribut->mapping->foreignKey);
            });
        } elseif ($assoc['type'] == 'oneToMany') {
            return $foreignRepo->findAllBy([$schemAttribut->mapping->foreignKey => intval($this->get('id'))]);
        } elseif ($assoc['type'] == 'manyToOne') {
            return $foreignRepo->findOneBy('id', intval($this->attributs[$schemAttribut->mapping->associatedKey]));
        } elseif ($assoc['type'] == 'oneToOne') {
            if($schemAttribut->mapping->containDependancy) {
                return $foreignRepo->findOneBy([$schemAttribut->mapping->table.'.id' => intval($this->attributs[$schemAttribut->mapping->associatedKey])]);
            } else {
                return $foreignRepo->findOneBy([$schemAttribut->mapping->foreignKey => $schemAttribut->mapping->table.'.id']);
            }
        }
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
}
