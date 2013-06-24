<?php

namespace XtractPDF\DocRenderer;

use XtractPDF\Model\Document;
use SimpleXMLElement;

/**
 * Render document model as JATS
 */
class JatsXmlRenderer implements RendererInterface
{
    /**
     * @return string  A machine-readable name (alpha-dash)
     */
    public static function getSlug()
    {
        return 'jats-xml';
    }

    // --------------------------------------------------------------
    
    /**
     * @return string  A human-friendly name
     */
    static function getName()
    {
        return "JATS XML Renderer";
    }

    // --------------------------------------------------------------
    
    static function getDescription()
    {
        return "Renders the document as serialized JATS XML";
    }

    // --------------------------------------------------------------

    static function getMime()
    {
        return 'application/xml';
    }

    // --------------------------------------------------------------

    /**
     * Render a document
     *
     * @param XtractPDF\Model\Document
     * @return mixed  A representation of the Document
     */
    public function render(Document $doc, array $options = array())
    {   
        $xmlObj = new SimpleXMLElement('<Article></Article>');        

        //Basic structure
        $front = $xmlObj->addChild('front');
        $body  = $xmlObj->addChild('body');
        $back  = $xmlObj->addChild('back');

        //Map Biblio-Metadata
        $jm = $front->addChild('journal-meta');
        $jm->addChild('issn', $doc->getMeta('issn'));
        $jm->addChild('journal-title-group')->addChild('journal-title', $doc->getMeta('journal'));
        $am = $front->addChild('article-meta');
        $am->addChild('title-group')->addChild('article-title', $doc->getMeta('title'));
        $am->addChild('article-id', $doc->getMeta('doi'))->addAttribute('pub-id-type', 'doi');
        $am->addChild('article-id', $doc->getMeta('pmid'))->addAttribute('pub-id-type', 'pmid');
        $am->addChild('isbn', $doc->getMeta('isbn'));
        $am->addChild('volume', $doc->getMeta('volume'));
        $am->addChild('issue', $doc->getMeta('issue'));
        $am->addChild('fpage', $doc->getMeta('startPage'));
        $am->addChild('lpage', $doc->getMeta('endPage'));
        $pd = $am->addChild('pub-date');
        $pd->addAttribute('pub-type', 'pub');
        $pd->addChild('year', $doc->getMeta('year'));
        $kws = $am->addChild('keyword-group');
        foreach($doc->getMeta('keywords') as $kw) {
            $kws->addChild('kwd', $kw);
        }

        //Map Authors
        $contribs = $am->addChild('contrib-group');
        foreach( $doc->authors as $author) {
            $auth = $contribs->addChild('contrib');
            $auth->addAttribute('contrib-type', 'author');
            $auth->addChild->addChild('string-name', $author->name);
        }

        //Map Abstract Sections
        //@TODO: THIS

        //Map content Sections
        //@TODO: This

        return $xmlObj->asXML();
    }

    // --------------------------------------------------------------

    /**
     * Serialize a rendered document
     *
     * @param XtractPDF\Model\Document
     * @return string
     */
    public function serialize(Document $document, array $options = array())
    {   
        return $this->render($document, $options);
    }

}

/* EOF: JatsXmlRenderer.php */