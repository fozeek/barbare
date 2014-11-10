<?php

namespace Barbare\Framework\Block;


class BlockManager
{

	protected $application;

	public function __construct($application)
	{
		$this->application = $application;
	}

	public function get($name)
	{
		$slice = explode('::', $name);
		$module = current($slice);

		$namespace = explode('.', next($slice));

		var_dump($module);
		var_dump($namespace);
		die();

		// Recupere le module, puis le block associé
		// TODO, rendre la class Block abstract
		// Faire une vérification si l'id est unique
		return new Block($name);
	}



}