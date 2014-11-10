<?php

namespace Barbare\Framework\View;

class Layout
{

    protected $path = 'app/design/';

    protected $template;
    public $content;
    protected $variables = [];

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function setVariable($key, $value)
    {
        $this->variables[$key] = $value;
    }

    public function setVariables($array)
    {
        $this->variables = array_merge($this->variables, $array);
    }

    public function render($content) 
    {
        $this->content = $content;
        extract($this->variables);
        include $this->path.$this->template.'.tpl';
    }

    public function __get($key)
    {
        if(array_key_exists($key, $this->variables)) {
            return $this->variables[$key];
        }
        else {
            return false;
        }
    }

}