<?php

namespace Barbare\Framework\Http;

class Session
{
    private $data;

    public function __construct($container)
    {
        $this->data = $_SESSION;
    }

    public function get($key)
    {
        return $this->data[$key];
    }

    public function add($key, $value)
    {
        $this->data[$key] = $value;
        $_SESSION[$key] = $value;
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function remove($key)
    {
        unset($this->data[$key]);
        unset($_SESSION[$key]);
    }

    public function addFlashMessage($message)
    {
        $data = is_array($message) ? $message : [$message];
        if (!$this->has('_flashmessages')) {
            $this->add('_flashmessages', $data);
        } else {
            $this->add('_flashmessages', array_merge($this->get('_flasmessages'), $data));
        }
    }

    public function getFlashMessages($message)
    {
        $fms = $this->get('_flasmessages');
        $this->remove('_flasmessages');

        return $fms;
    }
}
