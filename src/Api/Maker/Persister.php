<?php

namespace Barbare\Framework\Api\Maker;

class Persister
{

	protected $stackPath = [];

	public function run() {
		foreach ($this->stackPath as $path) {
			if(!is_dir($path)) {
				continue;
			}
			while($file = scandir($path)) {
				include $path;
			}
		}
		return $this;
	}

	public function addPath($path) {
		$this->stackPath[] = $path;
		return $this;
	}
}