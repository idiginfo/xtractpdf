<?php
  
/**
 *   XtractPDF - A PDF Content Extraction and Curation Tool
 *
 *   This program is free software under the GNU General Public License (v2)
 *   See LICENSE.md for a complete copy of the license
 *
 * @package     XtractPDF
 * @author      Florida State University iDigInfo (http://idiginfo.org)
 * @copyright   Copyright (C) 2013 Florida State University (http://fsu.edu)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt
 * @link        http://idiginfo.org
 */

// ------------------------------------------------------------------

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

        foreach(self::getPublicProperties() as $k) {
            $arr[$k] = $this->$k;
        }

        return $arr;
    } 

    // --------------------------------------------------------------

    public static function getPublicProperties()
    {
        $arr = array();

        foreach(get_class_vars(get_called_class()) as $k => $v) {
            if ($k{0} != '_' && $k != 'iterator') {
                $arr[] = $k;
            }
        }

        return $arr;
    }

}

/* EOF: Model.php */