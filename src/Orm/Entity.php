<?php

namespace Barbare\Framework\Orm;

use Barbare\Framework\Orm\QueryBuilder;
use Barbare\Framework\Orm\Repository\DbCollection;

class Entity
{
    private $repository;
    private $schema;

    public function __construct($repository)
    {
        $this->repository = $repository;
        $this->schema = $repository->getSchema();
    }

    public function get($attribut)
    {
        if (isset($this->attributs[$attribut])) {
            return $this->attributs[$attribut];
        } elseif ($this->schema->attributs->get($attribut)->mapping) {
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
