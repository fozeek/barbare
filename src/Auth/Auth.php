<?php

namespace Barbare\Framework\Auth;

use Barbare\Framework\Mvc\Component;

class Auth extends Component
{
    private $user = false;
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
        if ($container->get('session')->has('_id')) {
            $this->user = $container->get('model')->get('user')->findOneBy(['id' => $container->get('session')->get('_id')]);
        }
    }

    public function connect($pseudo, $password = null)
    {
        if(is_string($pseudo) && $password !== null) {
            $user = $this->container->get('model')->get('user')->findOneBy(['pseudo' => $pseudo]);
            if ($user && $user->get('password') == self::encrypt($password)) { // hash_equals for PHP >= 5.6
                $this->container->get('session')->add('_id', $user->get('id'));
                return $this->user = $user;
            }
            return $this->user = false;
        }
        $this->user = $pseudo;
        $this->container->get('session')->add('_id', $this->user->get('id'));
        return $this->user;
    }

    public function disconnect()
    {
        $this->container->get('session')->remove('_id');
        $this->user = false;
    }

    public function isAuthenticated()
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
