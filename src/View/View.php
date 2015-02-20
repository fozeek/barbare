<?php

namespace Barbare\Framework\View;

use Barbare\Framework\Util\Storage;

class View
{

	protected $path;

	protected $headers = [];
	protected $application;
	protected $layout;
	protected $template;
	protected $helpers;
	protected $content;
	protected $variables = [];

	public function __construct($application)
	{
		$this->application = $application;
		$this->layout = new Layout($this);

		$this->_loadHelpers();
	}

	protected function _loadHelpers() {
		$config = $this->application->getConfig()->read('helpers');
		$this->helpers = new Storage();
		foreach ($config as $key => $value) {
			$helper = false;
			if(is_string($value) && class_exists($value)) {
				$helper = new $value($this->application, $this);
			} elseif (is_callable($value)) {
				$helper = $value($this->application, $this);
			} else {
				$helper = $value;
			}
			$this->helpers->write(
				$key, 
				$helper
			);
		}
	}

	public function __get($key) 
	{
		if(array_key_exists($key, $this->variables)) {
			return $this->variables[$key];
		}
		return false;
	}

	public function __call($helper, $params)
	{
		if(is_object($this->helpers->read($helper))) {
			return call_user_func_array([$this->helpers->read($helper), '__invoke'], $params);
		} else {
			return call_user_func_array($this->helpers->read($helper), array_merge($params));
		}
	}

	public function addHeader($header)
	{
		$this->headers[] = $header;
	}

	public function setTemplate($template)
	{
		$this->template = $template;
	}

	public function setcontent($content)
	{
		$this->content = $content;
	}

	public function setLayout($layout)
	{
		$this->layout = $layout;
	}

	public function setPath($path)
	{
		$this->path = $path;
	}

	public function addBlock($block) 
	{
		$this->blocks[] = $block;
	}

	public function setVariable($key, $value)
	{
		$this->variables[$key] = $value;
	}

	public function setVariables($array)
	{
		$this->variables = array_merge($this->variables, $array);
	}

	public function render()
	{
		extract($this->variables);
		ob_start();
		include $this->path.$this->template.'.tpl';
		$content = ob_get_clean();
		$this->layout->render($content);
	}

	public function partial($template, $variables = array())
	{
		extract($this->variables);
		extract($variables);
		include $this->path.$template.'.tpl';
	}

	public function get($key)
	{
		if(property_exists($this, $key)) {
			return $this->$key;
		}
	}

}