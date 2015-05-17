<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Schema\Attribut;

class Mapping
{
    public $type;
    public $table;
    public $associatedTable;
    public $associatedKey;
    public $foreignKey;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function table($table)
    {
        $this->table = $table;
    }

    public function keys($associatedKey, $foreignKey)
    {
        $this->associatedKey = $associatedKey;
        $this->foreignKey = $foreignKey;
    }

    public function associatedTable($associatedTable)
    {
        $this->associatedTable = $associatedTable;
    }
}
