<?php

namespace Barbare\Framework\Block;

use Barbare\Framework\Util\Storage;

abstract class Block
{

	protected $name;
	protected $category;
	protected $code;
	protected $config;

	public function __construct($name)
	{
		$this->name = $name;
		$this->category = 'Seo';
	}

	public function setConfig($config)
	{
		$this->config = $config;
		$this->code = $this->config['key'];
		return $this;
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getCategory()
	{
		return $this->category;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function render()
	{
		// Retourne les données traitées pour l'affichage de la vue
		// Ces données sont prefixées pour na pas rentrer en conflit avec les variables de tous les autres blocks
		// Car le template pour rendre tous les blocks est dans un seul fichier
		// Or une variable peut contenir une template (si elle n'est pas modifiable par le createur de template)
		return [
			'title' => 'YEAH',
			'graph' => 'Ceci est un graph',
		];
	}



}