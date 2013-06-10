<?php

namespace XtractPDF\Extractor;

use XtractPDF\Library\ExtractorException;
use XtractPDF\Model\Document as DocumentModel;

/**
 * Extractor interface for different PDF extractors
 */
interface ExtractorInterface
{
    /**
     * @return string  A machine-readable name (alpha-dash)
     */
    static function getSlug();

    // --------------------------------------------------------------

    /**
     * @return string  A link to more information
     */
    static function getLink();

    // --------------------------------------------------------------
    
    /**
     * @return string  A human-friendly name
     */
    static function getName();

    // --------------------------------------------------------------
    
    /**
     * @return string  A description
     */
    static function getDescription();

    // --------------------------------------------------------------
    
    /**
     * Extract information from PDF
     *
     * @param  string  $filepath  Path to resource or other readable stream
     * @return string|boolean     Serialized version of extracted data (false upon fail)
     */
    function extract($filepath);

    // --------------------------------------------------------------

    /**
     * Map the extracted response to a model
     *
     * @param  mixed                    $output
     * @param  XtractPDF\Model\Document $model
     * @return XtractPDF\Model\Document
     */
    function map($output, DocumentModel $model);
}