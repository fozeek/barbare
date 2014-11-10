<?php

namespace Barbare\Framework\Acl\Config;

class Config
{
	
	$namespaces = [];

	public function __construct()
	{
	}

	public function namespace($namespace, $callback)
	{
		$this->namespaces[$namespace] = $callback;
	}
}