<?php

namespace Barbare\Framework\Orm;


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

    public function getInheritance()
    {
        return $this->get('_join_table_name');
    }

    private function _build($attribut)
    {
        $schemAttribut = $this->schema->get($attribut);
        $foreignRepo = $this->repository->getManager()->get($schemAttribut->mapping->table);
        if ($schemAttribut->mapping->type == 'manyToMany') {
            return $foreignRepo->findAllCallback(function ($qb) {
                $qb->from($this->schema->name.'_'.$foreignRepo->name)
                    ->where($schemAttribut->mapping->associatedKey, '=', $schemAttribut->mapping->foreignKey, false);
            });
        } elseif ($schemAttribut->mapping->type == 'oneToMany') {
            return $foreignRepo->findBy([$schemAttribut->mapping->foreignKey => intval($this->get('id'))]);
        } elseif ($schemAttribut->mapping->type == 'manyToOne') {
            return $foreignRepo->findOneBy('id', intval($this->attributs[$schemAttribut->mapping->associatedKey]));
        } elseif ($schemAttribut->mapping->type == 'oneToOne') {
            if ($schemAttribut->mapping->containDependancy) {
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
