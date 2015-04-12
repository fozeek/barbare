<?php

namespace Barbare\Framework\View;

use Barbare\Framework\Util\Container;

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
    protected $enableLayout = true;

    public function __construct($container)
    {
        $this->container = $container;
        $this->layout = new Layout($this);
        $this->helpers = new Container($container->get('application')->getConfig()->read('helpers'));
        $this->helpers->add('view', $this);
        $this->helpers->add('application', $container->get('application'));
    }

    public function getHelper($name)
    {
        return $this->helpers->get(strtolower($name));
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->variables)) {
            return $this->variables[$key];
        }

        return false;
    }

    public function __call($helper, $params)
    {
        $cb = $this->helpers->get($helper);
        if (is_object($cb)) {
            return call_user_func_array([$cb, '__invoke'], $params);
        }

        return call_user_func_array($cb, $params);
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

    public function disableLayout()
    {
        $this->enableLayout = false;
    }

    public function render($vars = array())
    {
        $this->setVariables($vars);
        extract($this->variables);
        ob_start();
        include $this->path.$this->template.'.tpl';
        $content = ob_get_clean();
        if ($this->enableLayout) {
            $this->layout->render($content);
        } else {
            echo $content;
        }
    }

    public function partial($template, $variables = array())
    {
        extract($this->variables);
        extract($variables);
        include $this->path.$template.'.tpl';
    }

    public function get($key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }
    }
}
