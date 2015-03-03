<?php

namespace Barbare\framework\Orm;

use PDO;
use PDOException;

// FAIRE UN SYSTEME DE CACHE AU LIEU DE CA !!
$GLOBALS["SQL_existing_table"] = array();

function is_assoc($arr)
{
    return array_keys($arr) !== range(0, count($arr) - 1);
}

/*
  Class User
  Description :
  -
  -
 */

class QueryBuilder {

    public static $TYPE_SELECT = "_SELECT";
    public static $TYPE_INSERT = "_INSERT";
    public static $TYPE_UPDATE = "_UPDATE";
    public static $TYPE_DELETE = "_DELETE";
    public static $COUNT = 0;
    public static $HISTO = array();
    public static $TYPE_NO_QUOTE = false;
    public static $OPE_DEFAULT = "=";
    public static $OPE_EQUAL = "=";
    public static $OPE_NOT_EQUAL = "!=";
    public static $OPE_UPPER_EQUAL = ">=";
    public static $OPE_LOWER_EQUAL = "<=";
    public static $OPE_UPPER = ">";
    public static $OPE_LOWER = "<";
    public static $OPE_IN = "IN";
    public static $OPE_NOT_IN = "NOT IN";
    private $OPE_LOGIC_TAB = array("where" => "", "andwhere" => "AND", "orwhere" => "OR");
    private $class;
    private $type;
    private $select;
    private $from;
    private $where;
    private $union = array();
    private $columns = array();
    private $values = array();
    private $orderby;
    private $limit;
    private $group;
    public static $_historique = array();

    static public function create() {
        return new QueryBuilder();
    }

    public function select($select = null) {
    if (is_array($select)) {
        $this->select = $select;
    } else {
        for ($cpt = 0; $cpt < func_num_args(); $cpt++)
        $this->select[] = func_get_arg($cpt);
    }
    return $this;
    }

    public function insert($into) {
        $this->type = self::$TYPE_INSERT;
        $this->class = $into;
        return $this;
    }

    public function update($table) {
        $this->type = self::$TYPE_UPDATE;
        $this->from = $table;
        return $this;
    }

    public function delete($table) {
        $this->type = self::$TYPE_DELETE;
        $this->from = $table;
        return $this;
    }

    public function columns($columns = null) {
        if (is_array($columns)) {
            $this->columns = $columns;
        } else {
            for ($cpt = 0; $cpt < func_num_args(); $cpt++)
            $this->columns[] = func_get_arg($cpt);
        }
        return $this;
    }

    public function values($values = null) {
        if (is_array($values)) {
            $this->values = $values;
        } else {
            for ($cpt = 0; $cpt < func_num_args(); $cpt++)
            $this->values[] = func_get_arg($cpt);
        }
        return $this;
    }

    /*
      Peut prendre 3 types de syntaxe en parametres

      columnsValues("collonne", "value"); // valeurs String
      columnsValues(array("collonne1", "collonne2"), array("value1", "value2")); // Deux tableaux
      columnsValues(array("collonne1" => "value1", "collonne2" => "value2")); // Tableau associatif

     */
    public function columnsValues($columns, $values = null) {
        $novalues = false; // Variable pour forcer le faite de ne pas prendre en compte $values
        if (!is_array($columns))
            $columns = array($columns);
        else {
            if (is_assoc($columns)) {
            $novalues = true;
            $tmpTab = $columns;
            $columns = array();
            foreach ($tmpTab as $key => $value) {
                $columns[] = $key;
                $values[] = $value;
            }
            } else {
            if ($values == null && sizeof($columns) == sizeof($values) && sizeof($values) > 0) {
                return new Error(7);
            }
            }
        }
        if (!$novalues) {
            if ($values != null) {
            if (!is_array($values))
                $values = array($values);
            else {
                if (is_assoc($values)) {
                return new Error(8);
                }
            }
            } else {
            echo "prout !!";
            return new Error(5);
            }
        }

        $this->columns = array_merge($this->columns, $columns);
        $this->values = array_merge($this->values, $values);
        return $this;
    }

    public function from($from) {
        $this->type = self::$TYPE_SELECT;
        if (is_array($from)) {
            foreach ($from as $value) {
            $this->from[] = $value;
            }
        } else {
            for ($cpt = 0; $cpt < func_num_args(); $cpt++)
            $this->from[] = func_get_arg($cpt);
        }
        return $this;
    }

    public static function n($class) {
        return new $class();
    }

    public function where($attribut, $condition = null, $param = null, $typeVar = true) {
        if (is_object($attribut))
            $this->where[] = $attribut;
        elseif ($condition == null)
            $this->where[] = $attribut;
        else
            $this->where[] = array("where", $attribut, $condition, $param, $typeVar);
        return $this;
    }

    public function union($table, $fields) {
        array_push($this->union, array($table, $fields));
        return $this;
    }

    public function andWhere($attribut, $condition = null, $param = null, $typeVar = true) {
        if (is_object($attribut))
            $this->where[] = $attribut;
        elseif ($condition == null)
            $this->where[] = $attribut;
        else
            $this->where[] = array("andwhere", $attribut, $condition, $param, $typeVar);
        return $this;
    }

    public function orWhere($attribut, $condition = null, $param = null, $typeVar = true) {
        if (is_object($attribut))
            $this->where[] = $attribut;
        elseif ($condition == null)
            $this->where[] = $attribut;
        else
            $this->where[] = array("orwhere", $attribut, $condition, $param, $typeVar);
        return $this;
    }

    public function orderBy($column, $way = null) {
        if (is_array($column))
            $this->orderby = $column;
        else
            $this->orderby = array($column, $way);
        return $this;
    }

    public function limit($start = 0, $end = null) {
        $this->limit = array($start, $end);
        return $this;
    }

    public function groupBy($group) {
        $this->group = $group;
        return $this;
    }

    private function getRequete() {
        $requete = "";
        if ($this->type == self::$TYPE_SELECT)
            $requete = $this->getselectRequete();
        elseif ($this->type == self::$TYPE_INSERT)
            $requete = $this->getInsertRequete();
        elseif ($this->type == self::$TYPE_UPDATE)
            $requete = $this->getUpdateRequete();
        elseif ($this->type == self::$TYPE_DELETE)
            $requete = $this->getDeleteRequete();
        else
            return new Error(1);
        return $requete;
    }

    private function getSelectRequete() {
        $this->class = ucfirst($this->from[0]);
        $requete = "SELECT ";
        // SELECT
        if (empty($this->select))
            $requete .= "*";
        elseif (is_array($this->select)) {
            $cpt = 0;
            foreach ($this->select as $value) {
            if ($cpt != 0)
                $requete .= ", ";
            $requete .= $value;
            $cpt++;
            }
        }
        else {
            $requete .= $this->select;
        }

        // FROM
        $requete .= " FROM ";
        $cpt = 0;
        foreach ($this->from as $value) {
            if ($cpt != 0)
            $requete .= " " . chr($cpt + 64) . ", ";
            $requete .= $value;
            $cpt++;
        }
        $requete .= " " . chr($cpt + 64) . "";

        // WHERE
        $requete .= $this->getWhereString();

        if (!empty($this->union)) {
            foreach ($this->union as $union) {
            $requete .= " UNION SELECT ";
            $cpt = 0;
            foreach ($union[1] as $value) {
                if ($cpt != 0)
                $requete .= ", ";
                $requete .= $value;
                $cpt++;
            }
            $requete .= " FROM $union[0] ";
            $requete .= " " . chr($cpt + 64) . "";
            $requete .= $this->getWhereString();
            }
        }

        if (!empty($this->group))
            $requete .= " GROUP BY " . $this->group;

        // ORDER BY
        if (!empty($this->orderby)) {
            $requete .= " ORDER BY " . $this->orderby[0] . " " . $this->orderby[1];
        }
        // LIMIT
        if (!empty($this->limit)) {
            $requete .= " LIMIT " . $this->limit[0];
            if ($this->limit[1] != null)
            $requete .= ", " . $this->limit[1];
        }

        return $requete;
    }

    private function getInsertRequete() {
        $requete = "INSERT INTO ";
        $requete .= mb_strtolower($this->class) . " (";
        $cpt = 0;
        foreach ($this->columns as $key => $value) {
            if ($cpt != 0)
            $requete .= ", ";
            $requete .= $value;
            $cpt++;
        }
        $requete .= ") VALUES (";
        $cpt = 0;
        foreach ($this->values as $key => $value) {
            if (is_string($value))
            $cote = '\''; else
            $cote = '';
            if (empty($value) && $value != "0") {
            $value = 'NULL';
            $cote = '';
            }
            if ($cpt != 0)
            $requete .= ", ";
            $requete .= $cote . addslashes($value) . $cote;
            $cpt++;
        }
        $requete .= ")";
        return $requete;
    }

    private function getUpdateRequete() {
        if (count($this->columns) == count($this->values) && count($this->values) > 0) {
            $requete = "UPDATE " . mb_strtolower($this->from) . " SET ";
            $cpt = 0;
            foreach ($this->columns as $key => $value) {
            if (is_string($this->values[$cpt]))
                $cote = '\''; else
                $cote = '';
            if ($cpt != 0)
                $requete .= ", ";
            $requete .= $value . " = " . $cote . addslashes($this->values[$cpt]) . $cote;
            $requete .= "";
            $cpt++;
            }
            if (!empty($this->where)) {
            $requete .= $this->getWhereString();
            }
            return $requete;
        }
        else
            return new Error(4);
    }

    private function getDeleteRequete() {
        if (true) {
            $requete = "DELETE FROM " . mb_strtolower($this->from) . " ";
            if (!empty($this->where))
                $requete .= $this->getWhereString();
            return $requete;
        }
        else
            return new Error(4);
    }

    public function fetchArray() {
        $requete = $this->getRequete();
        self::$COUNT += 1;
        self::$HISTO[] = $requete;
        return DbConnect::getConnection()->query($requete)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function query($requete) {
        return DbConnect::getConnection()->query($requete)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function exec($requete) {
        return DbConnect::getConnection()->exec($requete);
    }

    public function execute() {
        if ($this->type == self::$TYPE_INSERT || $this->type == self::$TYPE_UPDATE || $this->type == self::$TYPE_DELETE) {
            $requete = $this->getRequete();
            array_push(self::$_historique, $requete);
            if (DbConnect::getConnection()->exec($requete)) {
            if ($this->type == self::$TYPE_INSERT)
                return DbConnect::getConnection()->lastInsertId();
            else
                return true;
            }
            else
            return false;
        }
        else
            return false;
    }

    public function fetch($rang = 0) {
        array_push(self::$_historique, $this->getRequete());
        return DbConnect::getConnection()->query($this->getRequete())->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getWhereString() {
        $requete = "";
        if (!empty($this->where)) {
            $requete .= " WHERE ";
            foreach ($this->where as $key => $value) {
            if (is_array($value)) {
                $cote2 = (is_string($value[3]) && $value[4]) ? '\'' : '';
                if ($value[2] == "IN" || $value[2] == "NOT IN") {
                if (is_string($value[3][0]))
                    foreach ($value[3] as $key3 => $value3)
                    $value[3][$key3] = "'" . $value3 . "'";
                $args = "(" . implode(", ", $value[3]) . ")";
                }
                else
                $args = $value[3];
                $requete .= " " . $this->OPE_LOGIC_TAB[$value[0]] . " " . $value[1] . " " . $value[2] . " " . $cote2 . $args . $cote2 . " ";
            }
            elseif (is_string($value)) {
                $requete .= $value;
            } else {
                $requete .= $this->getWhereStringRecursive($value);
            }
            }
        }
        return $requete;
    }

    private function getWhereStringRecursive($object) {
        $requete = "(";
        foreach ($object->where as $key2 => $value2) {
            if (is_array($value)) {
            $cote2 = (is_string($value[3]) && $value[4]) ? '\'' : '';
            if ($value[2] == "IN" || $value[2] == "NOT IN") {
                if (is_string($value[3][0]))
                foreach ($value[3] as $key3 => $value3)
                    $value[3][$key3] = "'" . $value3 . "'";
                $args = "(" . implode(", ", $value[3]) . ")";
            }
            else
                $args = $value[3];
            $requete .= " " . $this->OPE_LOGIC_TAB[$value[0]] . " " . $value[1] . " " . $value[2] . " " . $cote2 . $args . $cote2 . " ";
            }
            elseif (is_string($value2)) {
            $requete .= $value2;
            }
            else
            $requete .= $this->getWhereStringRecursive($value2);
        }
        $requete .= ")";
        return $requete;
    }

    public function showRequete() {
        return $this->getRequete();
    }

}

?>