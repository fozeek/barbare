<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Sql;

class Attribut
{
    public $table;

    public $onModel;
    public $name;
    public $autoIncrement = false;
    public $primaryKey = false;
    public $type = 'text';
    public $unique = false;
    public $typeOptions = "";
    public $events = ['create' => [], 'save' => []];
    public $nullable = false;
    public $index = false;
    public $mapping = false;
    public $default = false;
    public $validate = false;

    public function __construct($table, $name, $onModel = true)
    {
        $this->onModel = $onModel;
        $this->table = $table;
        $this->name = $name;
    }

    public function getSql($ai = false)
    {
        return Sql::attribut($this, $ai);
    }

    public function type($type, $options = "")
    {
        $this->type = $type;
        $this->typeOptions = $options;
    }

    public function validate($callback)
    {
        $this->validate = $callback;
    }

    public function mapping($type, $cb)
    {
        $this->type = 'int';
        $this->typeOptions = '11';
        $this->mapping = new Mapping($this, $type);
        $cb($this->mapping);
    }

    public function on($events, $cb)
    {
        if (!is_array($events)) {
            $events = [$events];
        }
        foreach ($events as $event) {
            $this->events[$event][] = $cb;
        }
    }

    public function defaultValue($default)
    {
        $this->default = $default;
    }

    public function null()
    {
        $this->nullable = true;
    }

    public function unique()
    {
        $this->unique = true;
    }

    public function primaryKey()
    {
        $this->primaryKey = true;
    }

    public function index()
    {
        $this->index = true;
    }

    public function autoIncrement()
    {
        $this->autoIncrement = true;
        $this->type = 'int';
        $this->unique = true;
        $this->typeOptions = 11;
        $this->primaryKey = true;
    }
}
