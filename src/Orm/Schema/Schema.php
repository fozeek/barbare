<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Schema\Table;
use Barbare\Framework\Orm\DbConnect;
use Barbare\Framework\Orm\Sql;
use PDO;

class Schema
{
    private $tables = [];
    private $behaviors = [];
    private $db;
    private $build = false;
    private $instanciatedConstraints = [];

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function table($name, $cb, $onModel = true)
    {
        $this->tables[$name] = new Table($this, $name, $onModel);
        $cb($this->tables[$name]);
    }

    public function behavior($name, $cb)
    {
        $this->behaviors[$name] = $cb;
    }

    public function getBehavior($name)
    {
        if(!isset($this->behaviors[$name])) {
            return false;
        }
        return $this->behaviors[$name];
    }

    public function get($table)
    {
        if(!isset($this->tables[$table])) {
            return false;
        }
        return $this->tables[$table];
    }

    private function build()
    {
        if(!$this->build) {
            $this->build = true;
            $instanciatedConstraints = [];
            foreach ($this->tables as $table) {
                if(!$table->ephemeral) {
                    foreach ($table->attributs as $attribut) {
                        if($attribut->mapping) {
                            $mapping = $attribut->mapping;
                            if($mapping->type == 'manyToMany') {
                                // On ajoute la table de jointure au schema
                                $this->table($mapping->associatedTable, function($table) use ($mapping) {
                                    $table->attribut($mapping->associatedKey, function($attribut) {
                                        $attribut->type('int', 11);
                                        $attribut->index();
                                    });
                                    $table->attribut($mapping->foreignKey, function($attribut) {
                                        $attribut->type('int', 11);
                                        $attribut->index();
                                    });
                                }, false);
                                $instanciatedConstraints[] = [
                                    'associated' => ['table' => $mapping->associatedTable, 'column' => $mapping->associatedKey],
                                    'foreign' => ['table' => $table->name, 'column' => 'id'],
                                    'delete' => 'CASCADE',
                                    'update' => 'NO ACTION'
                                ];
                                $instanciatedConstraints[] = [
                                    'associated' => ['table' => $mapping->associatedTable, 'column' => $mapping->foreignKey],
                                    'foreign' => ['table' => $mapping->table, 'column' => 'id'],
                                    'delete' => 'CASCADE',
                                    'update' => 'NO ACTION'
                                ];
                            }
                            elseif($mapping->type == 'manyToOne' || ($mapping->type == 'oneToOne' && $mapping->containDependancy)) {
                                $table->attribut($mapping->associatedKey, function($attribut) {
                                    $attribut->type('int', 11);
                                    $attribut->index();
                                    $attribut->null();
                                }, false);
                                $instanciatedConstraints[] = [
                                    'associated' => ['table' => $table->name, 'column' => $mapping->associatedKey],
                                    'foreign' => ['table' => $mapping->table, 'column' => 'id'],
                                    'delete' => 'SET NULL',
                                    'update' => 'NO ACTION'
                                ];
                            }
                            elseif($mapping->type == 'oneToMany' || ($mapping->type == 'oneToOne' && !$mapping->containDependancy)) {
                                $this->get($mapping->table)->attribut($mapping->foreignKey, function($attribut) {
                                    $attribut->type('int', 11);
                                    $attribut->index();
                                    $attribut->null();
                                }, false);
                                $instanciatedConstraints[] = [
                                    'associated' => ['table' => $mapping->table, 'column' => $mapping->foreignKey],
                                    'foreign' => ['table' => $table->name, 'column' => 'id'],
                                    'delete' => 'SET NULL',
                                    'update' => 'NO ACTION'
                                ];
                            }
                        }
                    }
                }
            }
            $this->instanciatedConstraints = $instanciatedConstraints;
        }
        return $this->instanciatedConstraints;
    }

    public function getSql()
    {
        $sql = "";
        foreach ($this->tables as $name => $table) {
            $sql .= $table->getSql().PHP_EOL.PHP_EOL;
        }
        return $sql;
    }

    public function tree($model = false)
    {
        $this->build();
        $string = "";
        foreach ($this->tables as $table) {
            if((!$model && !$table->ephemeral) || ($model && $table->onModel)) {
                $string .= "\033[1;32m".(!$model ? $table->name : ucfirst($table->name))."\033[0m";
                if($table->join && $model) {
                    $string .= " extends \033[1;32m".ucfirst($table->join)."\033[0m";
                }
                $string .= PHP_EOL;
                $attributs = [];
                foreach ($table->attributs as $attribut) { 
                    if(
                        (!$model || ($model && $attribut->onModel))
                        && (($attribut->mapping && $model) || (!$attribut->mapping))
                    ) {
                        $options = [];
                        if($attribut->autoIncrement) {
                            $options[] = "AUTO_INCREMENT";
                        }
                        if($attribut->primaryKey) {
                            $options[] = "PRIMARY KEY";
                        } elseif($attribut->unique) {
                            $options[] = "UNIQUE";
                        } elseif($attribut->index) {
                            $options[] = "INDEX";
                        }
                        $needed = "\033[0m*";
                        $length = 20;
                        if($attribut->nullable || $attribut->mapping) {
                            $needed = "";
                            $length = 16;
                        }
                        $attributString = "├── \033[1;35m".str_pad($attribut->name.$needed, $length)."\033[0m";
                        if(!$model) {
                            $attributString .= "\033[0;37m ".$attribut->type."(".$attribut->typeOptions.") ".implode(" ", $options)."\033[0m";
                        } else {
                            if($attribut->mapping) {
                                $attributString .= "\033[0;37m ".ucfirst($attribut->mapping->table);
                                if($attribut->mapping->type == 'oneToMany' || $attribut->mapping->type == 'manyToMany') {
                                    $attributString .= "[]";
                                }
                                $attributString .= "\033[0m";
                            }
                        }
                        $attributs[] = $attributString;
                    }
                }
                if(count($attributs) > 0) {
                    end($attributs);
                    $attributs[key($attributs)] = "└── ".substr($attributs[key($attributs)], 10);
                    $string .= implode(PHP_EOL, $attributs);
                }
                $string .= PHP_EOL;
            }
        }
        return $string;
    }

    public function update()
    {
        $count = 0;
        $this->build();

        $data = DbConnect::getConnection()->query("SELECT * FROM `information_schema`.`COLUMNS` WHERE TABLE_SCHEMA = '".$this->db."' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC);
        $schema = [];
        foreach ($data as $value) {
            unset($value['TABLE_CATALOG']);
            unset($value['TABLE_SCHEMA']);
            if(!isset($schema[$value['TABLE_NAME']])) {
                $schema[$value['TABLE_NAME']] = [];
            }
            $schema[$value['TABLE_NAME']][$value['COLUMN_NAME']] = $value;
        }

        $sql = "";

        $dropAttrs = "";

        foreach ($this->tables as $table) {
            if(!$table->ephemeral) {
                if(array_key_exists($table->name, $schema)) {
                    // On cherche les ≠ entre les attributs
                    foreach ($table->attributs as $attribut) {
                        if(!$attribut->mapping) {
                            if(array_key_exists($attribut->name, $schema[$table->name])) {
                                $isAutoIncrement = $schema[$table->name][$attribut->name]['EXTRA'] == "auto_increment";
                                $isNullable = $schema[$table->name][$attribut->name]['IS_NULLABLE'] == "YES";
                                $isPrimaryKey = $schema[$table->name][$attribut->name]['COLUMN_KEY'] == "PRI";
                                // On teste si ils sont égaux
                                $same = true;
                                $type = $attribut->type;
                                if($attribut->typeOptions) {
                                    $type .= "(".$attribut->typeOptions.")";
                                }
                                if($type == 'boolean') {
                                    $type = 'tinyint(1)';
                                }
                                if(
                                    $schema[$table->name][$attribut->name]['COLUMN_DEFAULT'] != ($attribut->default === false ? NULL: $attribut->default)
                                    || $schema[$table->name][$attribut->name]['COLUMN_TYPE'] != $type
                                ) {
                                    $same = false;
                                }
                                if($isNullable != $attribut->nullable || $isAutoIncrement != $attribut->autoIncrement) {
                                    $same = false;
                                }
                                if(!$same) {
                                    $count++;
                                    $sql .= "ALTER TABLE `".$table->name."` CHANGE `".$attribut->name."` " .Sql::attribut($attribut).";".PHP_EOL; 
                                    if($attribut->primaryKey && $schema[$table->name][$attribut->name]['EXTRA'] != "auto_increment") { // Il faut ajouter l'index Primary key pour definir le champs en AUTO_INCREMENT
                                        if(!$isPrimaryKey) {
                                            $count++;
                                            $sql .= "ALTER TABLE `".$table->name."` ADD PRIMARY KEY(`".$attribut->name."`);".PHP_EOL;
                                        }
                                        $count++;
                                        $sql .= "ALTER TABLE `".$table->name."` CHANGE `".$attribut->name."` " .Sql::attribut($attribut, true).";".PHP_EOL;
                                    }
                                }
                                unset($schema[$table->name][$attribut->name]);
                            } else {
                                $count++;
                                $sql .= "ALTER TABLE `".$table->name."` ADD " .Sql::attribut($attribut).";".PHP_EOL;
                                if($attribut->primaryKey) { // Il faut ajouter l'index Primary key pour definir le champs en AUTO_INCREMENT
                                    $count++;
                                    $sql .= "ALTER TABLE `".$table->name."` ADD PRIMARY KEY(`".$attribut->name."`);".PHP_EOL;
                                    $count++;
                                    $sql .= "ALTER TABLE `".$table->name."` CHANGE `".$attribut->name."` " .Sql::attribut($attribut, true).";".PHP_EOL;
                                }
                            }
                        }
                    }
                    foreach ($schema[$table->name] as $attr) {
                        if($attr['COLUMN_KEY'] == "") {
                            $count++;
                            $sql .= "ALTER TABLE `".$table->name."` DROP `".$attr['COLUMN_NAME']."`;".PHP_EOL;
                        } else {
                            $count++;
                            $dropAttrs .= "ALTER TABLE `".$table->name."` DROP `".$attr['COLUMN_NAME']."`;".PHP_EOL;;
                        }
                    }
                } else {
                    $count = 0;
                    foreach ($table->attributs as $attr) {
                        if(!$attr->mapping) {
                            $count++;
                        }
                    }
                    if($count > 0) {
                        $sql .= Sql::table($table);
                    }
                }
            }
        }

        if($sql != "") {
            print("Tables & Columns".PHP_EOL);
            print($sql);
            try {
                DbConnect::getConnection()->exec($sql);
            } catch (\PDOException $e) {
                die($e->getMessage().PHP_EOL);
            }
            $sql = "";
        }

        // Indexs
        $data = DbConnect::getConnection()->query("SELECT * FROM `information_schema`.`STATISTICS` WHERE `INDEX_SCHEMA` LIKE '".$this->db."' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC);
        $indexes = [];
        foreach ($data as $value) {
            if(!isset($indexes[$value['TABLE_NAME']])) {
                $indexes[$value['TABLE_NAME']] = [];
            }
            $indexes[$value['TABLE_NAME']][$value['COLUMN_NAME']] = $value;
        }

        foreach ($this->tables as $table) {
            if(!$table->ephemeral) {
                if(array_key_exists($table->name, $indexes)) {
                    foreach ($table->attributs as $attribut) {
                        if(array_key_exists($attribut->name, $indexes[$table->name])) {
                            if(!$attribut->primaryKey && !$attribut->unique && !$attribut->index) { // On supprime l'index en trop
                                continue; // L'index sera supprimé dans le collecteur en fin de boucle
                            }
                            $isPrimary = $indexes[$table->name][$attribut->name]['INDEX_NAME'] == 'PRIMARY';
                            $isUnique = $indexes[$table->name][$attribut->name]['NON_UNIQUE'] == '0';
                            if(
                                $isPrimary != $attribut->primaryKey
                                || (!$attribut->primaryKey && ($isUnique != $attribut->unique)) // On s'occupe de l'unicité que si il ne s'agit pas d'une primary key
                            ) {
                                // On change l'index mal configuré
                                if($attribut->primaryKey) {
                                    $action = "PRIMARY KEY";
                                    $count++;
                                    $sql .= "ALTER TABLE `".$table->name."` DROP INDEX `".$indexes[$table->name][$attribut->name]['INDEX_NAME']."`, ADD ".$action." (`".$attribut->name."`);".PHP_EOL;
                                } elseif($attribut->unique) {
                                    if($isPrimary) {
                                        $count++;
                                        $sql .= "ALTER TABLE `".$table->name."` DROP PRIMARY KEY, ADD UNIQUE (`".$attribut->name."`);".PHP_EOL;
                                    } else {
                                        $count++;
                                        $sql .= "ALTER TABLE `".$table->name."` DROP INDEX `".$indexes[$table->name][$attribut->name]['INDEX_NAME']."`;".PHP_EOL;
                                        $count++;
                                        $sql .= "ALTER TABLE `".$table->name."` ADD UNIQUE (`".$attribut->name."`);".PHP_EOL;
                                    }
                                } elseif($attribut->index) {
                                    if($isPrimary) {
                                        $count++;
                                        $sql .= "ALTER TABLE `".$table->name."` DROP PRIMARY KEY, ADD INDEX (`".$attribut->name."`);".PHP_EOL;
                                    } else {
                                        $count++;
                                        $sql .= "ALTER TABLE `".$table->name."` DROP INDEX `".$indexes[$table->name][$attribut->name]['INDEX_NAME']."`;".PHP_EOL;
                                        $count++;
                                        $sql .= "ALTER TABLE `".$table->name."` ADD INDEX (`".$attribut->name."`);".PHP_EOL;
                                    }
                                }
                            }
                            unset($indexes[$table->name][$attribut->name]);
                        } else {
                            // On ajoute l'index
                            $sql .= $this->addIndex($table, $attribut, $count);
                        }
                    }
                    // On supprime les index en trop
                    foreach ($indexes[$table->name] as $index) {
                        $count++;
                        $sql .= "DROP INDEX `".$index['INDEX_NAME']."` ON `".$table->name."`;".PHP_EOL;
                    }
                } else { // On créer tous les index de la table
                    foreach ($table->attributs as $attribut) {
                        $sql .= $this->addIndex($table, $attribut, $count);
                        // Si auto_increment, on ajoute (car on l'a zappé si on vient de créer la table)
                        if($attribut->autoIncrement) {
                            $count++;
                            $sql .= "ALTER TABLE `".$table->name."` CHANGE `".$attribut->name."` " .Sql::attribut($attribut, true).";".PHP_EOL;
                        }
                    }
                }
            }
        }

        if($sql != "") {
            print("Indexs".PHP_EOL);
            print($sql);
            try {
                DbConnect::getConnection()->exec($sql);
            } catch (\PDOException $e) {
                die($e->getMessage().PHP_EOL);
            }
            $sql = "";
        }


        // On créer toutes les contraintes (tables de jointure créee)
        $constraints = DbConnect::getConnection()->query("SELECT * FROM `information_schema`.`KEY_COLUMN_USAGE` WHERE `CONSTRAINT_SCHEMA` LIKE '".$this->db."' AND `POSITION_IN_UNIQUE_CONSTRAINT` = 1 ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC);

        // on check les doublons
        for ($cpt1=0; $cpt1 < count($this->instanciatedConstraints); $cpt1++) { 
            for ($cpt2=$cpt1+1; $cpt2 < count($this->instanciatedConstraints); $cpt2++) { 
                if(
                    $this->instanciatedConstraints[$cpt2] != null
                    && $this->instanciatedConstraints[$cpt1]['associated']['table'] == $this->instanciatedConstraints[$cpt2]['associated']['table']
                    && $this->instanciatedConstraints[$cpt1]['associated']['column'] == $this->instanciatedConstraints[$cpt2]['associated']['column']
                    && $this->instanciatedConstraints[$cpt1]['foreign']['table'] == $this->instanciatedConstraints[$cpt2]['foreign']['table']
                    && $this->instanciatedConstraints[$cpt1]['foreign']['column'] == $this->instanciatedConstraints[$cpt2]['foreign']['column']
                ) {
                    $this->instanciatedConstraints[$cpt2] = null;
                }
            }
        }
        $this->instanciatedConstraints = array_filter($this->instanciatedConstraints);

        foreach ($this->instanciatedConstraints as $keyC => $constraint) {
            $founds = [];
            $found = false;
            foreach ($constraints as $key => $constraintDb) {
                if(
                    $constraintDb['TABLE_NAME'] == $constraint['associated']['table']
                    && $constraintDb['COLUMN_NAME'] == $constraint['associated']['column']
                    && $constraintDb['REFERENCED_TABLE_NAME'] == $constraint['foreign']['table']
                    && $constraintDb['REFERENCED_COLUMN_NAME'] == $constraint['foreign']['column']
                ) {
                    $found = true;
                    // On garde la clé pour des besoins plus tard (gestion des indexs)
                    $this->instanciatedConstraints[$keyC]['name'] = $constraintDb['CONSTRAINT_NAME'];
                    unset($constraints[$key]);
                }
            }
            if(!$found) {
                $count++;
                // on créer la contrainte
                $name = "BB".$constraint['associated']['table'].$constraint['associated']['column'].$constraint['foreign']['table'].$constraint['foreign']['column'];
                $sql .= "ALTER TABLE `".
                    $constraint['associated']['table'].
                    "` ADD FOREIGN KEY (`".
                    $constraint['associated']['column'].
                    "`) REFERENCES `".
                    $this->db.
                    "`.`".
                    $constraint['foreign']['table'].
                    "`(`".$constraint['foreign']['column'].
                    "`) ON DELETE ".$constraint['delete'].
                    " ON UPDATE ".
                    $constraint['update'].
                    ";"
                    .PHP_EOL;
            }
        }
        foreach ($constraints as $value) {
            // on delete la constraint
            $count++;
            $sql .= "ALTER TABLE ".$value['TABLE_NAME']." DROP FOREIGN KEY ".$value['CONSTRAINT_NAME'].";".PHP_EOL;
        }

        $sql .= $dropAttrs;


        if($sql != "") {
            print("Constraints".PHP_EOL);
            print($sql);
            try {
                DbConnect::getConnection()->exec($sql);
            } catch (\PDOException $e) {
                die($e->getMessage().PHP_EOL);
            }
            $sql = "";
        }

        
        return $count;
    }

    private function addIndex($table, $attribut, &$count)
    {
        $sql = "";
        if($attribut->primaryKey) {
            $count++;
            $sql = "ALTER TABLE `".$table->name."` ADD PRIMARY KEY(`".$attribut->name."`);".PHP_EOL;
        } elseif($attribut->unique) {
            $count++;
            $sql = "ALTER TABLE `".$table->name."` ADD UNIQUE(`".$attribut->name."`);".PHP_EOL;
        } elseif($attribut->index) {
            $count++;
            $sql = "ALTER TABLE `".$table->name."` ADD INDEX(`".$attribut->name."`);".PHP_EOL;
        }
        return $sql;
    }

    private function exec($requete, $debug = true) {
        try {
            $return = DbConnect::getConnection()->exec($requete);
        } catch (\PDOException $e) {
            if($debug) {
                return $e->getMessage();
            }
            return false;
        }
        return $return;
    }
}

/*

    ["TABLE_NAME"]=>
    string(4) "user"
    ["COLUMN_NAME"]=>
    string(6) "access"
    ["ORDINAL_POSITION"]=>
    string(1) "5"
    ["COLUMN_DEFAULT"]=>
    NULL
    ["IS_NULLABLE"]=>
    string(2) "NO"
    ["DATA_TYPE"]=>
    string(4) "text"
    ["CHARACTER_MAXIMUM_LENGTH"]=>
    string(5) "65535"
    ["CHARACTER_OCTET_LENGTH"]=>
    string(5) "65535"
    ["NUMERIC_PRECISION"]=>
    NULL
    ["NUMERIC_SCALE"]=>
    NULL
    ["CHARACTER_SET_NAME"]=>
    string(6) "latin1"
    ["COLLATION_NAME"]=>
    string(17) "latin1_swedish_ci"
    ["COLUMN_TYPE"]=>
    string(4) "text"
    ["COLUMN_KEY"]=>
    string(0) ""
    ["EXTRA"]=>
    string(0) ""
    ["PRIVILEGES"]=>
    string(31) "select,insert,update,references"
    ["COLUMN_COMMENT"]=>
    string(0) ""

*/


/*

// ALL COLUMNS
SELECT * FROM `information_schema`.`COLUMNS`

SHOW tables
SELECT
  *
FROM information_schema.TABLES
GROUP BY TABLE_SCHEMA;

SHOW FULL COLUMNS from user

SHOW INDEX FROM user

// Add columns
ALTER TABLE `user` ADD `echo` DATE NOT NULL , ADD `indent` TEXT NULL DEFAULT NULL COMMENT 'comm' ;

// ADD constraint
ALTER TABLE `user` ADD CONSTRAINT `name` FOREIGN KEY (`cat_id`) REFERENCES `potaufeu`.`comment`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

CREATE TABLE `coucou` (
    `id` int(11) NOT NULL,
  `tout` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `coucou`
--
ALTER TABLE `coucou`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `coucou`
--
ALTER TABLE `coucou`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

*/