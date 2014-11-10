<?php

namespace Barbare\Framework\Event;

use Barbare\Framework\Util\Storage;
use Barbare\Framework\Event\Event;

class EventManager
{

	protected $events;

	public function __construct()
	{
		$this->events = new Storage();
	}

	public function trigger($code, Event $event)
	{
		$event->setCallbacks($this->events->read($code));
		$event->run();
		return $this;
	}

	public function attach($code, $callback)
	{
		$this->events->write($code, $callback, true);
		return $this;
	}

}