<?php

namespace Barbare\Framework\Orm\Repository;

use Barbare\Framework\Orm\Repository\Structure\Structure;
use Barbare\Framework\Orm\QueryBuilder;

class Repository
{
    private $manager;
    private $structure;

    protected $tableName;
    protected $entityClassName;

    private $collections = []; // Cache all collections retrived in single execution

    private $methods = [];

    public function __construct($name, $manager)
    {
        $this->manager = $manager;
        //$this->structure = new Structure($this);
        //$this->structure($this->structure);

        // $this->methods = array(
        //     'findBy',
        //     'findBy{tag}' => function($rp, $tag) {
        //         $tag->match('tag', '[A-Z][a-z]*');
        //         return function($tags, $params) use ($rp) {
        //             $rp->_findBy(array($tags['attribut'] => $params[0]));
        //         };
        //     },
        //     'findAll',
        // );
    }

    public function getEntityClassName()
    {
        return $this->entityClassName;
    }

    public function setEntityClassName($entityClassName)
    {
        $this->entityClassName = $entityClassName;

        return $this;
    }

    public function getManager()
    {
        return $this->manager;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    // public function __call($function, $params)
    // {
    //     foreach ($this->methods as $name => $method) {
    //         //$tags = new Tags(); // Pour traiter les tags (et leurs regex)
    //         $callable = $method($this, $tags);

    //         $matches = array();
    //         $tags = array();
    //         if(preg_match('/^' . $name . '$/i', $function, $matches)) {
    //             $function = $method['_function'];
    //             $isCB = true;
    //             unset($method['_function']);
    //             if(is_array($method)) {
    //                 foreach ($method as $key => $value) {
    //                     next($matches);
    //                     $tags[$key] = current($matches);
    //                 }
    //             }
    //             break;
    //         }
    //     }
    //     if($isCB) {
    //         $function($this, $params, $tags);
    //     } else {
    //         call_user_func_array(array($this, $function), $params);
    //     }
    // }
    protected function beforeSave($data)
    {
        return $data;
    }

    protected function afterFind($data)
    {
        return $data;
    }

    public function create($attributs)
    {
        $res = QueryBuilder::create()->insert($this->tableName)->columnsValues($this->beforeSave($attributs))->execute();
        $entityClassName = $this->getEntityClassName();

        return new $entityClassName($this, $this->afterFind($attributs));
    }

    public function findById($id)
    {
        $entityClassName = $this->entityClassName;
        $data = QueryBuilder::create()->from($this->tableName)->where('id', '=', $id)->fetchArray();
        if ($data && isset($data[0])) {
            return new $entityClassName($this, $this->afterFind($data[0]));
        }

        return false;
    }

    public function findAll()
    {
        $collection = [];
        $entityClassName = $this->getEntityClassName();
        foreach (QueryBuilder::create()->from($this->tableName)->fetchArray() as $values) {
            $collection[] = new $entityClassName($this, $this->afterFind($values));
        }

        return new DbCollection($collection);
    }

    public function save($entity)
    {
        return QueryBuilder::create()->update($this->tableName)->where('id', '=', $entity->id)->columnsValues($entity->toArray());
    }
}
