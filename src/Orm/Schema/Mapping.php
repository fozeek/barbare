<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Schema\Attribut;

class Mapping
{
    public $attribut;

    public $type;
    public $table;
    public $associatedTable = false;
    public $associatedKey = false;
    public $foreignKey = false;
    public $containDependancy = false;

    public function __construct($attribut, $type)
    {
        $this->attribut = $attribut;
        $this->type = $type;
    }

    public function table($table)
    {
        $this->table = $table;
        if(!$this->associatedKey) {
            if($this->type != "manyToMany") {
                $this->associatedKey = $table.'_id';
            } else {
                $this->associatedKey = $this->attribut->table->name.'_id';
            }
        }
        if(!$this->foreignKey) {
            if($this->type != "manyToMany") {
                $this->foreignKey = $this->attribut->table->name.'_id';
            } else {
                $this->foreignKey = $table.'_id';
            }
        }
        if($this->type == "manyToMany" && !$this->associatedTable) {
            $this->associatedTable = $this->attribut->table->name.'_'.$table;
        }
    }

    public function keys($associatedKey, $foreignKey)
    {
        $this->associatedKey = $associatedKey;
        $this->foreignKey = $foreignKey;
    }

    public function containDependancy()
    {
        $this->containDependancy = true;
    }

    public function foreignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;
    }

    public function associatedKey($associatedKey)
    {
        $this->associatedKey = $associatedKey;
    }

    public function associatedTable($associatedTable)
    {
        $this->associatedTable = $associatedTable;
    }
}
