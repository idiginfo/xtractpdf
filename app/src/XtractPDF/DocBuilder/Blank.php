<?php

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