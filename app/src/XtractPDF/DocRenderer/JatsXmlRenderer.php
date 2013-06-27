<?php

namespace XtractPDF\DocRenderer;

use XtractPDF\Model;
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

    static function getExt()
    {
        return 'xml';
    }

    // --------------------------------------------------------------

    /**
     * Render a document
     *
     * @param XtractPDF\Model\Document
     * @return mixed  A representation of the Document
     */
    public function render(Model\Document $doc, array $options = array())
    {   
        $xmlObj = new SimpleXMLElement('<Article></Article>');        

        //Basic front and back structure
        $front = $xmlObj->addChild('front');
        $body  = $xmlObj->addChild('body');
        $back  = $xmlObj->addChild('back');

        //Map Biblio-Metadata
        $jm = $front->addChild('journal-meta');
        $jm->issn = $doc->getMeta('issn');
        $jtg = $jm->addChild('journal-title-group');
        $jtg->{'journal-title'} = $doc->getMeta('journal');

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
            $kws->kwd[] = $kw;
        }

        //Map Authors
        $contribs = $am->addChild('contrib-group');
        foreach( $doc->authors as $author) {
            $auth = $contribs->addChild('contrib');
            $auth->addAttribute('contrib-type', 'author');
            $auth->{'string-name'} = $author->name;
        }

        //Map Abstract Sections
        $this->recursizeSections($am->addChild('abstract'), $doc->abstract);
  
        //Map content Sections
        $this->recursizeSections($body, $doc->content);

        //Map citations
        $refList = $back->addChild('ref-list');
        foreach($doc->citations as $cite) {
            $refList->ref[] = $cite->content;
        }

        return $xmlObj->asXML();
    }

    // --------------------------------------------------------------

    /**
     * Serialize a rendered document
     *
     * @param XtractPDF\Model\Document
     * @return string
     */
    public function serialize(Model\Document $document, array $options = array())
    {   
        return $this->render($document, $options);
    }

    // --------------------------------------------------------------

    protected function recursizeSections(SimpleXMLElement $element, Model\DocumentContent $content)
    {
        $items = $content->sections->toArray();

        $currSec    = null;
        $currSubSec = null;

        while($item = array_shift($items)) {
            switch ($item->type) {
                case 'heading':

                    //Add existing section
                    if ($currSec) {

                        if ($currSubSec) {
                            $this->xmlAdopt($currSec, $currSubSec);
                        }

                        $this->xmlAdopt($element, $currSec);
                    }

                    //Create new section
                    $currSec = new SimpleXMLElement('<sec/>');
                    $currSec->title = $item->content;

                break;
                case 'subheading':

                    //If no currSection, auto add one
                    if ( ! $currSec) {
                        $currSec = new SimpleXMLElement('<sec/>');
                        $currSec->title = '';
                    }
                    //If existing subsection, close it out
                    elseif ($currSubSec) {
                        $this->xmlAdopt($currSec, $currSubSec);
                    }

                    $currSubSec = new SimpleXMLElement("<sec/>");
                    $currSubSec->title = $item->content;
                break;
                case 'paragraph': default:

                    if ($currSubSec) {
                        $currSubSec->p[] = $item->content;
                    }
                    elseif ($currSec) {
                        $currSec->p[] = $item->content;
                    }
                    else {
                        $element->p[] = $item->content;
                    }

                break;
            }
        }

        //Add final section if there is one
        if ($currSec) {

            if ($currSubSec) {
                $this->xmlAdopt($currSec, $currSubSec);
            }

            $this->xmlAdopt($element, $currSec);
        }

        return $element;
    }

    // --------------------------------------------------------------

    protected function xmlAdopt($root, $new, $namespace = null)
    {
        // first add the new node
        $node = $root->addChild($new->getName(), (string) $new, $namespace);

        // add any attributes for the new node
        foreach ($new->attributes() as $attr => $value) {
            $node->addAttribute($attr, $value);
        }

        // get all namespaces, include a blank one
        $namespaces = array_merge(array(null), $new->getNameSpaces(true));

        // add any child nodes, including optional namespace
        foreach ($namespaces as $space) {
            foreach ($new->children($space) as $child) {
               $this->xmlAdopt($node, $child, $space);
            }
        }
    }
}

/* EOF: JatsXmlRenderer.php */