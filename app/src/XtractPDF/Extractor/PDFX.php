<?php

namespace XtractPDF\Extractor;

use Guzzle\Http\Client as GuzzleClient;
use XtractPDF\Library\ExtractorException;
use XtractPDF\Model\Document as DocumentModel;
use SimpleXMLElement;

/**
 * PDFX Web Service -- NOT IN USE NOT IN USE
 */
class PDFX implements ExtractorInterface
{
    const API_URL = 'http://pdfx.cs.man.ac.uk/';

    // --------------------------------------------------------------

    /**
     * @var string  The python command to perform the conversion
     */
    private $guzzle;
    

    // --------------------------------------------------------------

    /**
     * Constructor
     *
     * @param Guzzle\Http\Client
     * @param string  The URL to the endpoint
     */
    public function __construct(GuzzleClient $client = null)
    {
        $this->guzzle = $client ?: new GuzzleClient();
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
     * @param  string $stream  Stream or filepath
     * @return string|boolean  False if could not be converted
     */
    public function extract($stream)
    {
        //Setup a new request
        $req = $this->guzzle->post(
            self::API_URL,
            array('Content-type' => 'application/pdf'),
            fopen($stream, 'r')
        );
        
        //Send it
        $resp = $req->send();

        //Return the response
        return (string) $resp->getBody();
    }

    // --------------------------------------------------------------

    public function map($output, DocumentModel $model)
    {
        //Convert the output to XML
        $xml = new SimpleXMLElement($output);
        $xml->registerXPathNamespace('DoCO', 'http://purl.org/spar/doco');

        //Metadata Mappings
        $model->setMeta('title', (string) $xml->xpath('//article/front//article-title')[0]);

        //Holder for biblio section
        $biblioSection = null;

        //Document Sections
        foreach($xml->xpath('//article/body//h1/parent::section') as $sec) {

            //Grab the bibliography section and move on
            if ($sec['class'] == 'DoCO:Bibliography') {
                $biblioSection = $sec;
                continue;
            }

            //Add it to the document
            list($title, $paras) = $this->extractSection($sec);
            $model->addNewSection($title, $paras);
        }

        //Document bibliography
        if ($biblioSection) {

            foreach($this->extractBiblio($biblioSection) as $cit) {
                $model->addNewCitation($cit);
            }
        }

        //Return the document
        return $model;
    }    

    // --------------------------------------------------------------

    private function extractBiblio(SimpleXMLElement $biblioSec)
    {
        $cites = array();

        foreach($biblioSec->xpath('region') as $reg) {
            $cites[] = $this->extractTextChunkRegion($reg);
        }

        return array_filter($cites);
    }

    // --------------------------------------------------------------

    private function extractSection(SimpleXMLElement $sec)
    {
        //Get the section title and setup the paragraphs
        $sectionTitle = (string) $sec->xpath('h1')[0];
        $paragraphs   = array();

        //If there is a text-chunk directly under the section, add that
        if ($sec->xpath('region')) {
            $paragraphs[] = $this->extractTextChunkRegion($sec->xpath('region')[0]);
        }

        //If there are subregions, then go through those
        foreach($sec->xpath('section') as $subSec) {

            //Add the header as its own paragraph
            $paragraphs[]  = (string) $subSec->xpath('h2')[0];
            
            foreach($subSec->xpath('region') as $ssReg) {
                $paragraphs[] = $this->extractTextChunkRegion($ssReg);
            }
        }

        //Clean out the empties
        $paragraphs = array_filter($paragraphs);
        
        //Return the title and paragraphs
        return array($sectionTitle, $paragraphs);
    }

    // --------------------------------------------------------------

    private function extractTextChunkRegion($region)
    {
        return ($region['class'] == 'DoCO:TextChunk')
            ? (string) $region
            : false;
    }

}

/* EOF: PDFX.php */