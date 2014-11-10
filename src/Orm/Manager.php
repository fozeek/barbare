<?php

namespace Barbare\Framework\Orm;

class Manager
{

	protected $models = [];
    protected $repositories = [];
    protected $behaviors = [];

	public function __construct($app)
	{

        foreach ($app->getconfig()->read('orm.behaviors') as $name => $behavior) {
            $this->behaviors[$name] = $behavior($app, $this);
        }
        foreach ($app->getConfig()->read('models') as $name => $repository) {
            $this->repositories[] = new $repository($this);
        }

        // echo '<pre>';
        // var_dump($this);die;
        // echo '</pre>';
	}

    public function getBehavior($name) 
    {
        return $this->behaviors[$name];
    }
	
}