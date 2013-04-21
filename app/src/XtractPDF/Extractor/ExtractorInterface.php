<?php

namespace XtractPDF\Extractor;
use XtractPDF\Library\ExtractorException;

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
     * @param  string  $filepath  Full Filepath to PDF
     * @return string|boolean Serialized version of extracted data (false upon fail)
     */
    function extract($filepath);

}