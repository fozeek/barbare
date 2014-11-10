<?php

namespace Barbare\Framework\Orm\Repository;

use Barbare\Framework\Orm\Repository\Structure\Structure;

class Repository
{
    private $manager;
    private $structure;

    private $collections = []; // Cache all collections retrived in single execution

    private $methods = [];

    public function __construct($manager) {
        $this->manager = $manager;
        $this->structure = new Structure($this);
        $this->structure($this->structure);
    }

    public function getManager()
    {
        return $this->manager;
    }

}