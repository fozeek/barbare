<?php

namespace Barbare\Framework\Auth;

use Barbare\Framework\Mvc\Component;

class Auth extends Component
{

    private $user;

    public function __construct($app, $controller)
    {
        if(isset($controller->Session->hasParam['id'])) {
            $this->user = $controller->Model->User->findById($controller->Session->getParam['id']);
        }
    }

    public function connect()
    {
        return false;
    }

    public function disconnect()
    {
        return false;
    }

    public function getUser()
    {
        return $this->user;
    }
}