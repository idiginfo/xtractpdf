<?php

namespace XtractPDF\Extractor;

use Guzzle\Http\Client as GuzzleClient;
use XtractPDF\Library\ExtractorException;

/**
 * PDFX Web Service -- NOT IN USE NOT IN USE
 */
class PDFX implements ExtractorInterface
{
    /**
     * @var string  The python command to perform the conversion
     */
    private $guzzle;
    
    /**
     * @var string  The endpoint URL
     */ 
    private $url;

    // --------------------------------------------------------------


    /**
     * Constructor
     *
     * @param Guzzle\Http\Client
     * @param string  The URL to the endpoint
     */
    public function __construct(GuzzleClient $client = null, $url = 'http://example.com/xxx')
    {
        $this->guzzle = $client ?: new GuzzleClient();
        $this->url    = $url;
    }
    
    // --------------------------------------------------------------

    static public function getSlug()
    {
        return 'pdfx';
    }

    // --------------------------------------------------------------

    static public function getName()
    {
        return "PDFX";
    }

    // --------------------------------------------------------------

    static public function getDescription()
    {
        return "A fully-automated PDF-to-XML conversion of scientific articles";        
    }

    // --------------------------------------------------------------

    static public function getLink()
    {
        return "http://pdfx.cs.man.ac.uk/";
    }

    // --------------------------------------------------------------

    /**
     * Extract text from PDF file
     *
     * @param string  $file    Realpath to file
     * @return string|boolean  False if could not be converted
     */
    public function extract($filepath)
    {
        throw new ExtractorException("Not yet implemented");
    }
}

/* EOF: PDFX.php */