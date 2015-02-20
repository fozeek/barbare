<?php

namespace Barbare\Framework\Orm\Repository;

use Barbare\Framework\Orm\Repository\Structure\Structure;

class Repository
{
    private $manager;
    private $structure;

    private $collections = []; // Cache all collections retrived in single execution

    private $methods = [];

    public function __construct($manager) {
        $this->manager = $manager;
        $this->structure = new Structure($this);
        $this->structure($this->structure);

        $this->methods = array(
            'findBy',
            'findBy{tag}' => function($rp, $tag) {
                $tag->match('tag', '[A-Z][a-z]*');
                return function($tags, $params) use ($rp) {
                    $rp->_findBy(array($tags['attribut'] => $params[0]));
                };
            },
            'findAll',
        );
    }

    public function getManager()
    {
        return $this->manager;
    }

    public function __call($function, $params)
    {
        foreach ($this->methods as $name => $method) {
            $tags = new Tags(); // Pour traiter les tags (et leurs regex)
            $callable = $method($this, $tags);

            $matches = array();
            $tags = array();
            if(preg_match('/^' . $name . '$/i', $function, $matches)) {
                $function = $method['_function'];
                $isCB = true;
                unset($method['_function']);
                if(is_array($method)) {
                    foreach ($method as $key => $value) {
                        next($matches);
                        $tags[$key] = current($matches);
                    }
                }
                break;
            }
        }
        if($isCB) {
            $function($this, $params, $tags);
        } else {
            call_user_func_array(array($this, $function), $params);
        }
    }

    private function _findBy($attr, $tags)
    {
        var_dump($attr, $tags);
    }

    private function _findAll($attr, $tags)
    {
        var_dump($attr, $tags);
    }

}