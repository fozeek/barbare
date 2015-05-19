<?php

namespace Barbare\Framework\Orm;

class Sql
{
    static public function table($table)
    {
        $string = "CREATE TABLE `".$table->name."` (".PHP_EOL;
        // $indexes = [];
        // $autoIncrements = [];
        $attributs = [];
        // $uniques = [];
        // $primaries = [];
        foreach ($table->attributs as $attribut) {
            if(!$attribut->mapping) {
                $attributs[] = $attribut->getSql();
                // if($attribut->index) {
                //     $indexes[] = $attribut;
                // }
                // if($attribut->primaryKey) {
                //     $primaries[] = $attribut;
                // }
                // if($attribut->autoIncrement) {
                //     $autoIncrements[] = $attribut;
                // }
                // if($attribut->unique) {
                //     $uniques[] = $attribut;
                // }
            }
        }
        $string .= '    '.implode(','.PHP_EOL.'    ', $attributs);
        $string .= PHP_EOL.") ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;".PHP_EOL;

        //indexes   =>  L'Updator gere lui meme les indexes
        // $modifiers = [];
        // foreach ($primaries as $attribut) {
        //     $modifiers[] = "ADD PRIMARY KEY (`".$attribut->name."`)";
        // }
        // foreach ($indexes as $attribut) {
        //     $modifiers[] = "ADD INDEX (`".$attribut->name."`)";
        // }
        // foreach ($uniques as $attribut) {
        //     $modifiers[] = "ADD UNIQUE (`".$attribut->name."`)";
        // }
        // foreach ($autoIncrements as $attribut) {
        //     $modifiers[] = "MODIFY ".$attribut->getSql(true);
        // }
        // if(count($modifiers)>0) {
        //     $string .= PHP_EOL."ALTER TABLE `".$table->name."`".PHP_EOL.implode(','.PHP_EOL, $modifiers) . ";";;
        // }

        return $string;
    }

    static public function attribut($attribut, $ai = false)
    {
        $string = "`".$attribut->name."` ";
        $string .= $attribut->type;
        if(!empty($attribut->typeOptions)) {
            $string .= "(".$attribut->typeOptions.")";
        }
        if(!$attribut->nullable) {
            $string .= " NOT NULL";
        }
        if($ai && $attribut->autoIncrement) {
            $string .= " AUTO_INCREMENT";
        }
        if($attribut->default !== false) {
            if($attribut->default === null) {
                $attribut->default = "NULL";
            } elseif($attribut->default != "CURRENT_TIMESTAMP") {
                $attribut->default = "'".$attribut->default."'";
            }
            $string .= " DEFAULT ".$attribut->default;
        }
        return $string;
    }
}
