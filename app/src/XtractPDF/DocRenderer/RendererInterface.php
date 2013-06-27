<?php

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