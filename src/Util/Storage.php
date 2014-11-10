<?php

namespace Barbare\Framework\Util;

class Storage
{

	protected $storage = array();

	public function __construct($storage = array())
	{
		$this->storage = $storage;
	}

	public function write($key, $value, $insertAfter = false)
	{
		$insert = &$this->storage;
		foreach (explode('.', $key) as $folder) {
			$insert = &$insert[$folder];
		}
		if($insertAfter && (is_array($insert) || $empty = empty($insert))) {
			if($empty) {
				$insert = array($value);
				return;
			}
			array_push($insert, $value);
			return;
		}
		$insert = $value;
	}

	public function read($key)
	{
		$result = $this->storage;
		foreach (explode('.', $key) as $value) {
			$result = $result[$value];
		}
		return $result;
	}

	public function toArray()
	{
		return $this->storage;
	}

	public function last()
	{
		return end($this->storage);
	}

}