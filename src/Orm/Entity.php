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
        } elseif ($this->schema->get($attribut) && $this->schema->get($attribut)->mapping) {
            return $this->attributs[$attribut] = $this->_build($attribut);
        }
        return false;
    }

    public function check()
    {
        $errors = [];
        foreach ($this->schema->attributs as $attribut) {
            // Check with custom validate
            if($attribut->validate) {
                if(is_callable($attribut->validate)) {
                    $cb = $attribut->validate->bindTo($this);
                    if(!$cb($this->get($attribut))) {
                        $errors[] = $attribut->name;
                    }
                } elseif(is_string($attribut->validate)) {
                    //check with Validator provider
                }
            }
            // check with database rules
        }
        return $errors;
    }

    public function save()
    {
        return $this->getRepository()->save($this);
    }

    public function getRepository()
    {
        return $this->repository;
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
            return $foreignRepo->findAllCallback(function ($qb) use ($schemAttribut) {
                $qb->from($schemAttribut->mapping->associatedTable)
                    ->where('A.id', '=', 'B.'.$schemAttribut->mapping->foreignKey, false);
            });
        } elseif ($schemAttribut->mapping->type == 'oneToMany') {
            return $foreignRepo->findBy([$schemAttribut->mapping->foreignKey => intval($this->get('id'))]);
        } elseif ($schemAttribut->mapping->type == 'manyToOne') {
            return $foreignRepo->findOneBy(['id' => intval($this->attributs[$schemAttribut->mapping->associatedKey])]);
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
