<?php

namespace XtractPDF\DocBuilder;

use XtractPDF\Model\Document as DocumentModel;

/**
 * Extractor interface for different PDF extractors
 */
interface BuilderInterface
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
     * Build a model from the raw PDF data
     *
     * @param  string $stream  Stream or filepath
     * @return string|boolean  Serialized version of extracted data (false upon fail)
     */
    function build($stream, DocumentModel $model);
}