<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Schema\Table;
use Barbare\Framework\Orm\DbConnect;
use Barbare\Framework\Orm\Sql;
use PDO;

class Schema
{
    private $tables = [];
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function table($name, $cb)
    {
        $this->tables[$name] = new Table($name);
        $cb($this->tables[$name]);
    }

    public function getSql()
    {
        $sql = "";
        foreach ($this->tables as $name => $table) {
            $sql .= $table->getSql().PHP_EOL.PHP_EOL;
        }
        return $sql;
    }

    public function getCurrentSchema()
    {

        // Indexs
        $constraints = DbConnect::getConnection()->query("SELECT * FROM `information_schema`.`KEY_COLUMN_USAGE` WHERE `CONSTRAINT_SCHEMA` LIKE '".$this->db."' AND `POSITION_IN_UNIQUE_CONSTRAINT` = 1 ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC);
        // $constraints = [];
        // foreach ($data as $value) {
        //     unset($value['TABLE_CATALOG']);
        //     unset($value['TABLE_SCHEMA']);
        //     if(!isset($constraints[$value['TABLE_NAME']])) {
        //         $constraints[$value['TABLE_NAME']] = [];
        //     }
        //     $constraints[$value['TABLE_NAME']][$value['COLUMN_NAME']] = $value;
        // }

        $instanciatedConstraints = [];
        $tables = [];
        foreach ($this->tables as $table) {
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
                        });
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
                    elseif($mapping->type == 'manyToOne') {
                        $instanciatedConstraints[] = [
                            'associated' => ['table' => $table->name, 'column' => $attribut->name],
                            'foreign' => ['table' => $mapping->table, 'column' => 'id'],
                            'delete' => 'SET NULL',
                            'update' => 'NO ACTION'
                        ];
                    }
                    elseif($mapping->type == 'oneToMany') {
                        $instanciatedConstraints[] = [
                            'associated' => ['table' => $mapping->table, 'column' => $mapping->foreignKey],
                            'foreign' => ['table' => $table->name, 'column' => 'id'],
                            'delete' => 'SET NULL',
                            'update' => 'NO ACTION'
                        ];
                    }
                    elseif($mapping->type == 'oneToOne') {
                        $instanciatedConstraints[] = [
                            'associated' => ['table' => $table->name, 'column' => $attribut->name],
                            'foreign' => ['table' => $mapping->table, 'column' => 'id'],
                            'delete' => 'SET NULL',
                            'update' => 'NO ACTION'
                        ];
                    }
                }
            }
        }


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

        foreach ($this->tables as $table) {
            if(array_key_exists($table->name, $schema)) {
                // On cherche les ≠ entre les attributs
                foreach ($table->attributs as $attribut) {
                    if($attribut->onUpdate) {
                        if(array_key_exists($attribut->name, $schema[$table->name])) {
                            unset($schema[$table->name][$attribut->name]);
                            $sql .= "ALTER TABLE `".$table->name."` CHANGE `".$attribut->name."` " .Sql::attribut($attribut).";".PHP_EOL;
                        } else {
                            $sql .= "ALTER TABLE `".$table->name."` ADD " .Sql::attribut($attribut).";".PHP_EOL;
                        }
                    }
                }
                foreach ($schema[$table->name] as $attr) {
                    $sql .= "ALTER TABLE `".$table->name."` DROP `".$attr['COLUMN_NAME']."`;".PHP_EOL;
                }
            } else {
                $sql .= Sql::table($table);
            }
        }

        // Indexs
        $data = DbConnect::getConnection()->query("SELECT * FROM `information_schema`.`STATISTICS` WHERE `INDEX_SCHEMA` LIKE '".$this->db."' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC);
        $indexes = [];
        foreach ($data as $value) {
            unset($value['TABLE_CATALOG']);
            unset($value['TABLE_SCHEMA']);
            if(!isset($indexes[$value['TABLE_NAME']])) {
                $indexes[$value['TABLE_NAME']] = [];
            }
            $indexes[$value['TABLE_NAME']][$value['COLUMN_NAME']] = $value;
        }

        foreach ($this->tables as $table) {
            if(array_key_exists($table->name, $indexes)) {
                foreach ($table->attributs as $attribut) {
                    if(array_key_exists($attribut->name, $indexes[$table->name])) {
                        $indexAttribut = $indexes[$table->name][$attribut->name];
                        unset($indexes[$table->name][$attribut->name]);
                        if($attribut->unique && $indexAttribut['NON_UNIQUE'] != 0) {
                            $sql .= "DROP INDEX `".$indexAttribut['INDEX_NAME']."` ON ".$table->name.";".PHP_EOL;
                            $sql .= "ALTER TABLE `".$table->name."` ADD UNIQUE(`".$attribut->name."`);".PHP_EOL;
                        } elseif($attribut->primaryKey && $indexAttribut['INDEX_NAME'] != 'PRIMARY') {
                            $sql .= "DROP INDEX `".$indexAttribut['INDEX_NAME']."` ON ".$table->name.";".PHP_EOL;
                            $sql .= "ALTER TABLE `".$table->name."` ADD PRIMARY KEY(`".$attribut->name."`);".PHP_EOL;
                        } elseif($attribut->index && !$attribut->unique && !$attribut->primaryKey && ($indexAttribut['NON_UNIQUE'] == 0 || $indexAttribut['INDEX_NAME'] == 'PRIMARY')) {
                            $sql .= "DROP INDEX `".$indexAttribut['INDEX_NAME']."` ON ".$table->name.";".PHP_EOL;
                            $sql .= "ALTER TABLE `".$table->name."` ADD INDEX(`".$attribut->name."`);".PHP_EOL;
                        }
                    } else {
                        if($attribut->unique) {
                            $sql .= "ALTER TABLE `".$table->name."` ADD UNIQUE(`".$attribut->name."`);".PHP_EOL;
                        } elseif($attribut->primaryKey) {
                            $sql .= "ALTER TABLE `".$table->name."` ADD PRIMARY KEY(`".$attribut->name."`);".PHP_EOL;
                        } elseif($attribut->index) {
                            $sql .= "ALTER TABLE `".$table->name."` ADD INDEX(`".$attribut->name."`);".PHP_EOL;
                        }
                    }
                }
                foreach ($indexes[$table->name] as $index) {
                    $sql .= "DROP INDEX `".$index['INDEX_NAME']."` ON `".$table->name."`;".PHP_EOL;
                }
            } else { // On créer tous les index de la table
                foreach ($table->attributs as $attribut) {
                    if($attribut->unique) {
                        $sql .= "ALTER TABLE `".$table->name."` ADD UNIQUE(`".$attribut->name."`);".PHP_EOL;
                    } elseif($attribut->primaryKey) {
                        $sql .= "ALTER TABLE `".$table->name."` ADD PRIMARY KEY(`".$attribut->name."`);".PHP_EOL;
                    } elseif($attribut->index) {
                        $sql .= "ALTER TABLE `".$table->name."` ADD INDEX(`".$attribut->name."`);".PHP_EOL;
                    }
                }
            }
        }



        // On créer toutes les contraintes (tables de jointure créee)
        // on check les doublons
        for ($cpt1=0; $cpt1 < count($instanciatedConstraints); $cpt1++) { 
            for ($cpt2=$cpt1+1; $cpt2 < count($instanciatedConstraints); $cpt2++) { 
                if(
                    $instanciatedConstraints[$cpt2] != null
                    && $instanciatedConstraints[$cpt1]['associated']['table'] == $instanciatedConstraints[$cpt2]['associated']['table']
                    && $instanciatedConstraints[$cpt1]['associated']['column'] == $instanciatedConstraints[$cpt2]['associated']['column']
                    && $instanciatedConstraints[$cpt1]['foreign']['table'] == $instanciatedConstraints[$cpt2]['foreign']['table']
                    && $instanciatedConstraints[$cpt1]['foreign']['column'] == $instanciatedConstraints[$cpt2]['foreign']['column']
                ) {
                    $instanciatedConstraints[$cpt2] = null;
                }
            }
        }
        $instanciatedConstraints = array_filter($instanciatedConstraints);

        foreach ($instanciatedConstraints as $constraint) {
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
                    unset($constraints[$key]);
                }
            }
            if(!$found) {
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
            // on delete la contraint
            $sql .= "ALTER TABLE ".$value['TABLE_NAME']." DROP FOREIGN KEY ".$value['CONSTRAINT_NAME'].";".PHP_EOL;
        }

        


        print($sql);
        return DbConnect::getConnection()->exec($sql);
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