<?php

namespace Barbare\Framework\Orm;

use Barbare\Framework\Orm\Repository\Repository;

class Manager
{
    protected $models = [];
    protected $repositories = [];
    protected $behaviors = [];

    public function __construct($app)
    {
        DbConnect::addUser('default', [
            'host' => $app->getConfig()->read('db.host'),
            'database' => $app->getConfig()->read('db.database'),
            'user' => $app->getConfig()->read('db.user'),
            'password' => $app->getConfig()->read('db.password'),
        ]);

        DbConnect::connect('default');

        foreach ($app->getconfig()->read('orm.behaviors') as $name => $behavior) {
            $this->behaviors[$name] = $behavior($app, $this);
        }
        foreach ($app->getConfig()->read('models') as $name => $repository) {
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
