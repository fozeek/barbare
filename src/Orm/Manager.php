<?php

namespace Barbare\Framework\Orm;

use Barbare\Framework\Orm\Repository\Repository;

class Manager
{
    protected $models = [];
    protected $repositories = [];
    protected $behaviors = [];

    public function __construct($container)
    {
        DbConnect::addUser('default', [
            'host' => $container->get('application')->getConfig()->read('db.host'),
            'database' => $container->get('application')->getConfig()->read('db.database'),
            'user' => $container->get('application')->getConfig()->read('db.user'),
            'password' => $container->get('application')->getConfig()->read('db.password'),
        ]);

        DbConnect::connect('default');

        foreach ($container->get('application')->getconfig()->read('orm.behaviors') as $name => $behavior) {
            $this->behaviors[$name] = $behavior($container->get('application'), $this);
        }
        foreach ($container->get('application')->getConfig()->read('models') as $name => $repository) {
            if (is_string($repository)) {
                $this->repositories[$name] = new $repository($name, $this);
            } else {
                $this->repositories[$name] = $repository(new Repository($name, $this));
            }
        }
    }

    public function getBehavior($name)
    {
        return $this->behaviors[$name];
    }

    public function get($repository)
    {
        return $this->repositories[$repository];
    }
}
