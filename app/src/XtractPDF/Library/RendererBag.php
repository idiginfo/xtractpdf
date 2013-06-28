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

use XtractPDF\DocRenderer\RendererInterface;
use Pimple;

/**
 * Doc Renderer Bag
 */
class RendererBag extends Pimple
{
    /**
     * @param Pimple
     */
    private $bag;

    // --------------------------------------------------------------

    public function __construct(array $renderers = array())
    {
        $this->set($renderers);
    }

    // --------------------------------------------------------------

    public function set(array $renderers)
    {
        $this->bag = new Pimple();
        foreach($renderers as $renderer) {
            $this->add($renderer);
        }
    }   

    // --------------------------------------------------------------

    public function add(RendererInterface $renderer)
    {
        $this->bag[$renderer::getSlug()] = $renderer;
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

/* EOF: RendererBag.php */