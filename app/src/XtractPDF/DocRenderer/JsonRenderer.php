<?php

namespace XtractPDF\DocRenderer;

use Doctrine\Common\Collections\Collection;
use XtractPDF\Model\Document;
use XtractPDF\Core\Model;
use Traversable;

/**
 * Document Renderer Interface
 */
class JsonRenderer implements RendererInterface
{
    /**
     * @return string  A machine-readable name (alpha-dash)
     */
    public static function getSlug()
    {
        return 'json';
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
        return json_encode($this->objToArray($document));
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

/* EOF: JsonRenderer.php */