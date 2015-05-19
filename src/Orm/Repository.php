<?php

namespace Barbare\Framework\Orm;

use Barbare\Framework\Orm\QueryBuilder;
use Barbare\Framework\Orm\Repository\DbCollection;

class Repository
{
    private $manager;
    private $schema;

    public function __construct($manager, $schema)
    {
        $this->manager = $manager;
        $this->schema = $schema;
    }

    public function getManager()
    {
        return $this->manager;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function findAll()
    {
        $collection = [];
        $qb = QueryBuilder::create()->from($this->schema->name);
        if($this->schema->join) {
            $qb->join('LEFT', $this->schema->join, 'A.id = '.$this->schema->join.'.id');
        }
        foreach ($qb->fetchArray() as $values) {
            $collection[] = new Entity($this, $values);
        }

        return new DbCollection($collection);
    }
}
