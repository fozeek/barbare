<?php

namespace Barbare\Framework\Orm;

use PDO;
use PDOException;

class DbConnect
{
    private static $connection;
    private static $users;
    public static function addUser($name, $params)
    {
        self::$users[$name] = $params;
    }
    public static function getConnection()
    {
        return self::$connection;
    }
    public static function connect($name)
    {
        try {
            self::$connection = new PDO('mysql:host='.self::$users[$name]["host"].';dbname='.self::$users[$name]["database"], self::$users[$name]["user"], self::$users[$name]["password"], array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $users = self::$users;
            unset($users[$name]);
            $connected = false;
            foreach ($users as $key => $value) {
                $connected = true;
                try {
                    self::$connection = new PDO('mysql:host='.$value["host"].';dbname='.$value["database"], $value["user"], $value["password"], array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                    self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException $e) {
                    $connected = false;
                }
                if ($connected) {
                    break;
                }
            }
            if (!$connected) {
                header("Location:/setconfig.php");
            }
        }
    }
}
