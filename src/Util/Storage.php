<?php

namespace Barbare\Framework\Util;

use IteratorAggregate;
use ArrayIterator;

class Storage implements IteratorAggregate
{
    protected $storage = array();
    protected $position = 0;

    public function __construct($storage = array())
    {
        if ($storage instanceof self) {
            $storage = $storage->toArray();
        }
        $this->storage = $storage;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->storage);
    }

    public function write($key, $value, $insertAfter = false)
    {
        $insert = &$this->storage;
        foreach (explode('.', $key) as $folder) {
            $insert = &$insert[$folder];
        }
        if ($insertAfter && (is_array($insert) || $empty = empty($insert))) {
            if ($empty) {
                $insert = array($value);

                return $this;
            }
            array_push($insert, $value);

            return $this;
        }
        $insert = $value;

        return $this;
    }

    public function read($key, $preserve = true)
    {
        $result = $this->storage;
        foreach (explode('.', $key) as $value) {
            if (!array_key_exists($value, $result)) {
                return false;
            }
            $result = $result[$value];
        }
        // On retourne une nouvelle instance de Storage si la recherche correpond aux spécifications d'un storage
        if (
            is_array($result)
            && $preserve
        ) {
            return new Storage($result);
        } else {
            return $result;
        }
    }

    public function toArray()
    {
        return $this->storage;
    }

    public function last()
    {
        return end($this->storage);
    }
}
