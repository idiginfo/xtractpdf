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

use Guzzle\Http\Client as GuzzleClient;
use XtractPDF\Model;
use SimpleXMLElement;

/**
 * Blank Document Builder
 */
class Blank implements BuilderInterface
{
    // --------------------------------------------------------------

    static public function getSlug()
    {
        return 'blank';
    }

    // --------------------------------------------------------------

    static public function getName()
    {
        return "Blank";
    }

    // --------------------------------------------------------------

    static public function getDescription()
    {
        return "Generates a blank data model from the PDF to be manually filled in";        
    }

    // --------------------------------------------------------------

    /**
     * Extract text from PDF file
     *
     * @param  string $stream  Stream or filepath
     * @return string|boolean  False if could not be converted
     */
    public function build($stream, Model\Document $doc)
    {
        //Don't do anything with the stream, just return the doc as is
        return $doc;
    }

}

/* EOF: Blank.php */