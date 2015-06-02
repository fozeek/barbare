<?php

namespace Barbare\Framework\View;

use Barbare\Framework\Util\Container;

class View
{
    protected $messages = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

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

    private function getCodeMessage($code)
    {
        return $this->messages[$code];
    }
}
