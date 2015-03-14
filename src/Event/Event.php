<?php

namespace Barbare\Framework\Event;

use Barbare\Framework\Util\Storage;

class Event
{
    protected $data;
    protected $callbacks;
    protected $propagation = true;
    protected $cpt = 0;

    public function __construct($data)
    {
        $this->data = ($data instanceof Storage) ? $data : new Storage($data);
    }

    public function getData()
    {
        return $this->data;
    }

    public function setCallbacks($callbacks)
    {
        $this->callbacks = new Storage($callbacks);
    }

    public function addCallback($callback)
    {
        $this->callbacks->add($callback);
    }

    public function stopPropagation()
    {
        $this->propagation = false;
    }

    public function run()
    {
        $this->propagation = true;
        while ($this->cpt < count($this->callbacks) && $this->propagation) {
            call_user_func_array($this->callbacks->read($this->cpt, false), [$this]);
            $this->cpt++;
        }
    }
}
