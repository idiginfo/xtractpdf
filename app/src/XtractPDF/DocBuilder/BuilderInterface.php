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