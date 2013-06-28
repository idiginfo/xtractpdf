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

namespace XtractPDF\PdfDataHandler;

interface PdfDataHandlerInterface
{
    /**
     * Save the data PDF
     *
     * @param string   $identifier  A unique identifier for this document
     * @param resrouce $streamId    A stream that can be opened and read by fopen()
     */
    function save($identifier, $streamId);

    /**
     * Remove the data for a PDF
     *
     * @param string $identifier  An identifier
     */
    function del($identifer);

    /**
     * Stream the data for the PDF
     *
     * @param string   $identifier  An identifier
     * @return string  Contents of the PDF
     */
    function stream($identifier);

    /**
     * Get the an identifier that can be opened by fopen('...', 'r');
     *
     * @param  string $identifier  An identifier
     * @return string Location that can be opened by fopen('...', 'r');
     */
    function streamuri($identifier);
}

/* EOF: PdfDataHandlerInterface.php */