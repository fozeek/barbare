<?php

namespace Barbare\Framework\Orm\Entity;

class Entity
{
    public function __construct($attributs)
    {
        $this->attributs = $attributs;
    }

    public function __get($attribut)
    {
        return $this->attributs[$attribut];
    }
}