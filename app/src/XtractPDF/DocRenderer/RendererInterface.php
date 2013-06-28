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

namespace XtractPDF\DocRenderer;

use XtractPDF\Model\Document;

/**
 * Document Renderer Interface
 */
interface RendererInterface
{
    /**
     * @return string  A machine-readable name (alpha-dash)
     */
    static function getSlug();

    // --------------------------------------------------------------
    
    /**
     * @return string  A human-friendly name
     */
    static function getName();

    // --------------------------------------------------------------
    
    static function getDescription();

    // --------------------------------------------------------------

    /**
     * @return string  The MIME type to use in case the rendering is transmitted via HTTP
     */
    static function getMime();

    // --------------------------------------------------------------

    /**
     * @return string  Return the extension (sans dot) to use for downloading rendered documents from this method
     */
    static function getExt();

    // --------------------------------------------------------------

    /**
     * Render a document
     *
     * @param XtractPDF\Model\Document
     * @return mixed  A representation of the Document
     */
    function render(Document $document, array $options = array());

    // --------------------------------------------------------------

    /**
     * Serialize the rendered version of the document
     *
     * Typically does the same thing as render(), except in certain cases
     *
     * @param XtractPDF\Model\Document
     * @return string  A serialized representation of the rendered Document
     */
    function serialize(Document $document, array $optoins = array());
}

/* EOF: RendererInterface.php */