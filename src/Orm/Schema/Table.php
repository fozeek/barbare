<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Schema\Attribut;

class Table
{
    public $name;
    public $attributs = [];
    public $timestamps = false;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function attribut($name, $cb)
    {
        $this->attributs[$name] = $attribut = new Attribut($name);
        $cb($attribut);
    }

    public function timestamps()
    {
        $this->timestamps = true;
    }

    public function getSql()
    {
        $sql = "CREATE TABLE `".$this->name."` (".PHP_EOL;
        $indexes = [];
        $autoIncrements = [];
        $attributs = [];
        $uniques = [];
        foreach ($this->attributs as $attribut) {
            $attributs[] = $attribut->getSql();
            if($attribut->index) {
                $indexes[] = $attribut;
            }
            if($attribut->primaryKey) {
                $primaries[] = $attribut;
            }
            if($attribut->autoIncrement) {
                $autoIncrements[] = $attribut;
                $primaries[] = $attribut;
            }
            if($attribut->unique) {
                $uniques[] = $attribut;
            }

        }
        $sql .= '    '.implode(','.PHP_EOL.'    ', $attributs);
        $sql .= PHP_EOL.") ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;".PHP_EOL;

        //indexes
        $modifiers = [];
        foreach ($primaries as $attribut) {
            $modifiers[] = "ADD PRIMARY KEY (`".$attribut->name."`)";
        }
        foreach ($indexes as $attribut) {
            $modifiers[] = "ADD INDEX (`".$attribut->name."`)";
        }
        foreach ($uniques as $attribut) {
            $modifiers[] = "ADD UNIQUE (`".$attribut->name."`)";
        }
        foreach ($autoIncrements as $attribut) {
            $modifiers[] = "MODIFY ".$attribut->getSql()." AUTO_INCREMENT";
        }
        if(count($modifiers)>0) {
            $sql .= PHP_EOL."ALTER TABLE `".$this->name."`".PHP_EOL.implode(','.PHP_EOL, $modifiers) . ";";;
        }

        return $sql;
    }
}


/*
ADD UNIQUE(`slug`);
*/  