<?php

namespace Barbare\Framework\Auth;

use Barbare\Framework\Mvc\Component;

class Auth extends Component
{
    private $user = false;

    public function __construct($app, $controller)
    {
        $this->controller = $controller;
        if ($this->controller->Session->has('id')) {
            $this->user = $controller->Model->get('user')->findById($controller->Session->get('id'));
        }
    }

    public function connect($pseudo, $password)
    {
        $user = $this->controller->Model->get('user')->findByPseudo($pseudo);
        if ($user && $user->get('password') == self::encrypt($password)) { // hash_equals for PHP >= 5.6
            $this->controller->Session->add('id', $user->get('id'));

            return $this->user = $user;
        } else {
            return $this->user = false;
        }
    }

    public function disconnect()
    {
        $this->controller->Session->remove('id');
        $this->user = false;
    }

    public function isConnected()
    {
        return $this->user !== false;
    }

    public function getUser()
    {
        return $this->user;
    }

    public static function encrypt($password)
    {
        return md5(crypt($password, 'MY&SALT@VERY#COOL?THUG'));
    }
}
