<?php
  
/**
 * XtractPDF - A PDF Content Extraction and Curation Tool
 *
 * This program is free software under the GNU General Public License (v2)
 * See LICENSE.md for a complete copy of the license
 *
 * @package     XtractPDF
 * @author      Florida State University iDigInfo (http://idiginfo.org)
 * @copyright   Copyright (C) 2013 Florida State University (http://fsu.edu)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt
 * @link        http://idiginfo.org
 */

// ------------------------------------------------------------------

namespace XtractPDF\Library;

use XtractPDF\DocBuilder\BuilderInterface;
use Pimple;

/**
 * Doc Builder Bag
 */
class BuilderBag extends Pimple
{
    /**
     * @param Pimple
     */
    private $bag;

    // --------------------------------------------------------------

    public function __construct(array $builders = array())
    {
        $this->set($builders);
    }

    // --------------------------------------------------------------

    public function set(array $builders)
    {
        $this->bag = new Pimple();
        foreach($builders as $builder) {
            $this->add($builder);
        }
    }   

    // --------------------------------------------------------------

    public function add(BuilderInterface $builder)
    {
        $this->bag[$builder::getSlug()] = $builder;
    }

    // --------------------------------------------------------------

    public function getAll()
    {
        $out = array();

        foreach($this->bag->keys() as $k) {
            $out[$k] = $this->bag[$k];
        }

        return $out;
    }

    // --------------------------------------------------------------

    public function get($slug)
    {
        return $this->bag[$slug];
    }
}

/* EOF: BuilderBag.php */