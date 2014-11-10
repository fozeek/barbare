<?php

namespace Barbare\Framework\Api\Maker;

class Form
{
	const My_CUSTOM_MUTLISELECT = 'mycustomselect';

	protected $type;

	public function type($type)
	{
		$this->type = $type;
		return $this;
	}
}