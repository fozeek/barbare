<?php

namespace Barbare\Framework\Orm\Repository\Structure;

class Field
{
    private $structure;

    private $params = ['name', 'autoIncrement', 'type', 'size', 'null', 'interclassement'];
    private $values = [];

    private $behavior;

    public function __construct($name, $structure)
    {
        $this->structure = $structure;
        $this->name($name);
    }

    public function __call($name, $params)
    {
        if (in_array($name, $this->params)) {
            $this->values[$name] = $params;

            return $this;
        }

        return false;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->params)) {
            return $this->values[$name];
        }

        return false;
    }

    public function behavior($name, $callback)
    {
        $this->behavior = $this->structure->getRepository()->getManager()->getBehavior($name)->instance($callback);
    }
}
