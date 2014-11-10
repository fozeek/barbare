<?php

namespace Barbare\Framework\Orm\Repository\Structure\Behavior;

class Behavior
{
    public function instance($callback)
    {
        $clone = clone $this;
        $callback($clone);
        return $clone;
    }
}