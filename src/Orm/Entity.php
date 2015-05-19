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

    private function _build($attribut)
    {
        $shemAttribut = $this->schema->attributs->get($attribut);
        if(!$shemAttribut->mapping) {
            return false;
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
