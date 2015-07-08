<?php

namespace Barbare\Framework\View;

class Layout
{
    protected $view;

    protected $path = 'app/design/';
    protected $template;
    public $content;
    protected $variables = [];

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function getVariable($key)
    {
        if (!isset($this->variables[$key])) {
            return false;
        }

        return $this->variables[$key];
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function setVariable($key, $value)
    {
        $this->variables[$key] = $value;
        return $this;
    }

    public function setVariables($array)
    {
        $this->variables = array_merge($this->variables, $array);
        return $this;
    }

    public function render($content)
    {
        $this->content = $content;
        extract($this->variables);
        include $this->path.$this->template.'.tpl';
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->variables)) {
            return $this->variables[$key];
        } else {
            return false;
        }
    }

    public function __call($helper, $params)
    {
        return $this->view->__call($helper, $params);
    }
}
