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
use RuntimeException;

class FilePdfHandler implements PdfDataHandlerInterface
{
    /**
     * @var string
     */
    private $basepath;

    // --------------------------------------------------------------

    public function __construct($filepath)
    {
        //Set basepath
        $this->basepath = rtrim($filepath, '/') . '/';

        //Check writable
        if ( ! is_writable($this->basepath)) {
            throw new RuntimeException(sprintf(
                "The FilePDFHandler needs to be able to write to the configured path (check permissions): %s",
                $this->basepath
            ));
        }
    }

    // --------------------------------------------------------------

    public function streamuri($identifier)
    {
        return $this->resolveFileName($identifier);
    }

    // --------------------------------------------------------------

    public function stream($identifier)
    {
        readfile($this->streamuri($identifier));
    }

    // --------------------------------------------------------------

    public function save($identifier, $streamId)
    {
        $instream  = fopen($streamId, 'r');
        $outstream = fopen($this->resolveFileName($identifier), 'w');

        while ( ! feof($instream)) {
            fwrite($outstream, fread($instream, 8192));
        }

        fclose($instream);
        fclose($outstream);
    }    

    // --------------------------------------------------------------

    public function del($identifier)
    {
        unlink($this->resolveFileName($identifier));
    }

    // --------------------------------------------------------------

    private function resolveFileName($identifier)
    {
        return $this->basepath . $identifier . '.pdf';
    }

}

/* EOF: FilePdfHandler.php */
