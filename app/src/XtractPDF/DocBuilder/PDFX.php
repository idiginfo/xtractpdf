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
 * PDFX Document Builder
 */
class PDFX implements BuilderInterface
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
        return "A fully-automated PDF-to-XML conversion of scientific articles (http://pdfx.cs.man.ac.uk/)";        
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
        //Setup a new request
        $req = $this->guzzle->post(
            self::API_URL,
            array('Content-type' => 'application/pdf'),
            fopen($stream, 'r')
        );
        
        //Send it
        $resp = $req->send();

        //Return the response
        return $this->map((string) $resp->getBody(), $doc);
    }

    // --------------------------------------------------------------

    /**
     * Map the properties in the PDFX response to our model
     *
     * @param string                   $respBody  Raw response body from PDFX
     * @param XtractPDF\Model\Document $doc       Document Model to apply the output to
     * @return XtractPDF\Model\Document
     */
    protected function map($respBody, Model\Document $doc)
    {
        //Convert the output to XML
        $xml = new SimpleXMLElement($respBody);
        $xml->registerXPathNamespace('DoCO', 'http://purl.org/spar/doco');

        //Metadata Mappings
        if ($xml->xpath('//article/front//article-title')) {
            $tmp = $xml->xpath('//article/front//article-title');
            $doc->setMeta('title', (string) $tmp[0]);
        }

        //Holder for biblio section
        $biblioSection = null;

        //Document Content
        $docContent = new Model\DocumentContent();
        foreach($xml->xpath('//article/body//h1/parent::section') as $sec) {

            //Grab the bibliography section and move on
            if ($sec['class'] == 'DoCO:Bibliography') {
                $biblioSection = $sec;
                continue;
            }

            //Extract section
            list($title, $paras) = $this->extractSection($sec);

            //Add it to the document
            $docContent->addSection(new Model\DocumentSection($title, 'heading'));
            foreach($paras as $para) {
                $docContent->addSection(new Model\DocumentSection($para, 'paragraph'));
            }
        }
        $doc->setContent($docContent);

        //Document bibliography
        if ($biblioSection) {
            foreach($this->extractBiblio($biblioSection) as $cit) {
                $doc->addCitation(new Model\DocumentCitation($cit));
            }
        }

        //Return the document
        return $doc;
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
        $tmp = $sec->xpath('h1');
        $sectionTitle = (string) $tmp[0];
        $paragraphs   = array();

        //If there is a text-chunk directly under the section, add that
        if ($sec->xpath('region')) {
            $tmp = $sec->xpath('region');
            $paragraphs[] = $this->extractTextChunkRegion($tmp[0]);
        }

        //If there are subregions, then go through those
        foreach($sec->xpath('section') as $subSec) {

            //Add the header as its own paragraph
            $tmp = $subSec->xpath('h2');
            $paragraphs[]  = (string) $tmp[0];
            
            foreach($subSec->xpath('region') as $ssReg) {
                $paragraphs[] = $this->extractTextChunkRegion($ssReg);
            }
        }

        //Clean out empty paragraphs
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