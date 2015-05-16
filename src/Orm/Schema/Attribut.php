<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Schema\Attribut;

class Attribut
{
    public $name;
    public $autoIncrement = false;
    public $type = 'text';
    public $unique = false;
    public $typeOptions = "";
    public $events = [];
    public $nullable = false;
    public $index = false;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getSql()
    {
        return "`".$this->name."` ".$this->sqlType()." ".$this->sqlNull();
    }

    private function sqlType()
    {
        $return = $this->type;
        if(!empty($this->typeOptions)) {
            $return .= "(".$this->typeOptions.")";
        }
        return $return;
    }

    private function sqlNull()
    {
        return !$this->nullable ? "NOT NULL": "";
    }

    private function sqlAutoIncrement()
    {
        return $this->autoIncrement ? "AUTO_INCREMENT": "";
    }

    public function type($type, $options = "")
    {
        $this->type = $type;
        $this->typeOptions = $options;
    }

    public function on($event, $cb)
    {
        $this->events[$event] = $cb;
    }

    public function unique()
    {
        $this->unique = true;
    }

    public function index()
    {
        $this->index = true;
    }

    public function autoIncrement()
    {
        $this->autoIncrement = true;
    }
}
