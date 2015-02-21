<?php

namespace Barbare\Framework\Auth;

use Barbare\Framework\Mvc\Component;

class Auth extends Component
{

    private $user = false;

    public function __construct($app, $controller)
    {
        if(isset($controller->Session->hasParam['id'])) {
            $this->user = $controller->Model->User->findById($controller->Session->getParam['id']);
        }
    }

    public function connect($pseudo, $password)
    {
        $user = $controller->Model->User->findByPseudo($pseudo);
        if($user && $user->getPassword() == md5($password)) {
            return $this->user = $user;
        } else {
            return $this->user = false;
        }
    }

    public function disconnect()
    {
        $this->user = false;
    }

    public function getUser()
    {
        return $this->user;
    }
}