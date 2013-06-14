<?php

namespace XtractPDF\Core;

use IteratorAggregate, ArrayIterator, Countable;

/**
 * Abstract Model Class
 */
abstract class Model implements IteratorAggregate, Countable
{
    private $iterator;

    // --------------------------------------------------------------

    public function __get($item)
    {
        if ($item{0} != '_') {
            return $this->$item;
        }
    }

    // --------------------------------------------------------------

    public function __isset($item)
    {
        return ($item{0} != '_') ? isset($this->$item) : false;
    }

    // --------------------------------------------------------------

    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    }

    // --------------------------------------------------------------

    public function count()
    {
        return count($this->toArray());
    }

    // --------------------------------------------------------------

    public function toArray()
    {
        $arr = array();

        foreach(get_object_vars($this) as $k => $v) {
            if ($k{0} != '_' && $k != 'iterator') {
                $arr[$k] = $v;
            }
        }
        return $arr;
    } 
}

/* EOF: Model.php */