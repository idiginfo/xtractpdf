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

    /**
     * Render a document
     *
     * @param XtractPDF\Model\Document
     * @return mixed  A representation of the Document
     */
    function render(Document $document, array $options = array());
}

/* EOF: RendererInterface.php */