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

namespace XtractPDF\DocRenderer;

use XtractPDF\Model\Document;

/**
 * Document Renderer Interface as Multi-Dimensional Array
 */
class ArrayRenderer implements RendererInterface
{
    /**
     * @return string  A machine-readable name (alpha-dash)
     */
    public static function getSlug()
    {
        return 'array';
    }

    // --------------------------------------------------------------
    
    /**
     * @return string  A human-friendly name
     */
    static function getName()
    {
        return "Array";
    }

    // --------------------------------------------------------------
    
    static function getDescription()
    {
        return "Renders the document model as a PHP array";
    }

    // --------------------------------------------------------------

    static function getMime()
    {
        return 'text/plain';
    }
    
    // --------------------------------------------------------------

    static function getExt()
    {
        return 'txt';
    }

    // --------------------------------------------------------------

    /**
     * Render a document
     *
     * @param XtractPDF\Model\Document
     * @return mixed  A representation of the Document
     */
    public function render(Document $document, array $options = array())
    {   
        return $this->objToArray($document);
    }

    // --------------------------------------------------------------

    /**
     * Serialize a rendered document
     *
     * @param XtractPDF\Model\Document
     * @return string
     */
    public function serialize(Document $document, array $options = array())
    {   
        return var_export($this->render($document, $options));
    }

    // --------------------------------------------------------------

    /**
     * Recursively convert an object with toArray() method into an array
     *
     * Any objects that have protected properties in which to represent
     * must implement the public toArray() method
     *
     * @param mixed $item
     * @return array
     */
    private function objToArray($item)
    {
        $arr = array();

        foreach($item->toArray() as $k => $v) {

            if (is_object($v) && is_callable(array($v, 'toArray'))) {
                $arr[$k] = $this->objToArray($v);
            }
            else {
                $arr[$k] = $v;    
            }
            
        }

        return $arr;
    }
}

/* EOF: ArrayRenderer.php */