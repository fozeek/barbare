<?php

namespace Barbare\Framework\Orm;

use Barbare\Framework\Orm\Repository\Repository;
use Barbare\Framework\Orm\Schema\Schema;

class Manager
{
    protected $models = [];
    protected $repositories = [];
    protected $behaviors = [];
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;

        DbConnect::addUser('default', [
            'host' => $container->get('application')->getConfig()->read('db.host'),
            'database' => $container->get('application')->getConfig()->read('db.database'),
            'user' => $container->get('application')->getConfig()->read('db.user'),
            'password' => $container->get('application')->getConfig()->read('db.password'),
        ]);

        DbConnect::connect('default');

        // foreach ($container->get('application')->getconfig()->read('orm.behaviors') as $name => $behavior) {
        //     $this->behaviors[$name] = $behavior($container->get('application'), $this);
        // }
    }

    public function getBehavior($name)
    {
        return $this->behaviors[$name];
    }

    public function get($name)
    {
        if (!isset($this->repositories[$name])) {
            $repository = $this->container->get('application')->getConfig()->read('models.'.$name);
            if (is_string($repository)) {
                $this->repositories[$name] = new $repository($name, $this);
            } else {
                $this->repositories[$name] = $repository(new Repository($name, $this));
            }
        }

        return $this->repositories[$name];
    }

    public function importShema($cb)
    {
        $schema = new Schema($this->container->get('application')->getConfig()->read('db.database'));
        $cb($schema);

        return $schema;
    }
}
