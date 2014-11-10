<?php

namespace Barbare\Framework\Api\Maker;

class Attribut
{

	protected $name;
	protected $default;
	protected $canBeUpdated = false;
	protected $canBeSet = false;
	protected $displayName;
	protected $isNull = false;
	protected $maxSize;
	protected $isNegative = true;
	protected $autoIncrement = false;
	protected $isUnique = false;
	protected $type;
	protected $isRequired = false;
	protected $matchRegex;
	protected $update;
	protected $get;
	protected $save;
	protected $delete;
	protected $find;
	protected $order;
	protected $asserts = [];

	protected $form;

	public function __construct($name)
	{
		$this->name = $name;
		$this->form = new Form();
	}
	
	public function update($update)
	{
		$this->update = $update;
		return $this;
	}

	public function get($get)
	{
		$this->get = $get;
		return $this;
	}

	public function save($save)
	{
		$this->save = $save;
		return $this;
	}

	public function find($find)
	{
		$this->find = $find;
		return $this;
	}

	public function delete($delete)
	{
		$this->delete = $delete;
		return $this;
	}

	public function order($order)
	{
		$this->order = $order;
		return $this;
	}             

	public function byDefault($default) // uglyname (default throw an PHP error)
	{
		$this->default = $default;
		return $this;
	}

	public function isNull($isNull)
	{
		$this->isNull = $isNull;
		return $this;
	}

	public function assert($assertCallback)
	{
		$this->asserts[] = $assertCallback;
		return $this;
	}

	public function isNegative($isNegative)
	{
		$this->isNegative = $isNegative;
		return $this;
	}

	public function autoIncrement($autoIncrement)
	{
		$this->autoIncrement = $autoIncrement;
		return $this;
	}

	public function type($type, $options = array())
	{
		$this->type = [$type, $options];
		return $this;
	}

	public function isUnique($isUnique)
	{
		$this->isUnique = $isUnique;
		return $this;
	}

	public function matchRegex($matchRegex)
	{
		$this->matchRegex = $matchRegex;
		return $this;
	}

	public function isRequired($isRequired)
	{
		$this->isRequired = $isRequired;
		return $this;
	}

	public function maxSize($maxSize)
	{
		$this->maxSize = $maxSize;
		return $this;
	}

	public function canBeUpdated($canBeUpdated)
	{
		$this->canBeUpdated = $canBeUpdated;
		return $this;
	}

	public function form($callback)
	{
		$callback($this->form);
		return $this;
	}

	public function displayName($displayName)
	{
		$this->displayName = $displayName;
		return $this;
	}

	public function canBeSet($canBeSet)
	{
		$this->canBeSet = $canBeSet;
		return $this;
	}
}