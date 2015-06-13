<?php

namespace Barbare\Framework\Orm;

use Barbare\Framework\Orm\Schema\Schema;

class MManager
{
    protected $models = [];
    protected $repositories = [];
    protected $behaviors = [];
    protected $container;
    protected $schema;

    public function __construct($container)
    {
        $this->container = $container;
        $this->schema = $this->importSchema($container->get('application')->getConfig()->read('db.schema'));

        DbConnect::addUser('default', [
            'host' => $container->get('application')->getConfig()->read('db.host'),
            'database' => $container->get('application')->getConfig()->read('db.database'),
            'user' => $container->get('application')->getConfig()->read('db.user'),
            'password' => $container->get('application')->getConfig()->read('db.password'),
        ]);

        DbConnect::connect('default');
    }

    public function getBehavior($name)
    {
        return $this->behaviors[$name];
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function get($name)
    {
        if (!isset($this->repositories[$name])) {
            if ($schema = $this->schema->get($name)) {
                if ($schema->repositoryClassName) {
                    $repositoryClassName = $schema->repositoryClassName;
                    $this->repositories[$name] = new $repositoryClassName($this, $schema);
                } else {
                    $this->repositories[$name] = new Repository($this, $schema);
                }
            } else {
                return false;
            }
        }

        return $this->repositories[$name];
    }

    public function importSchema($cb)
    {
        $schema = new Schema($this->container->get('application')->getConfig()->read('db.database'));
        $cb($schema);

        return $schema;
    }
}
