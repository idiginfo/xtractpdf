<?php

namespace XtractPDF\Library;

use XtractPDF\Model;
use RuntimeException;

/**
 * Build a Document Model from a POST API Request
 *
 * @TODO: Move this?  But where?
 */
class DocumentAPIHandler
{
    /**
     * Build model from POST request
     *
     * @param string $data  Raw JSON from POST request
     * @param XtarctPDF\Model\Docuemnt $doc  Document object to populate
     */
    public function build($data, Model\Document $doc)
    {
        $postData = json_decode($data);

        if ( ! $postData) {
            throw new RuntimeException("Could not interpret document data");
        }

        //Set is Complete? (true or false)
        $doc->markComplete($postData->isComplete);

        //Set biblio meta
        foreach($postData->biblioMeta as $bm) {
            $doc->setMeta($bm->name, $bm->value);
        }

        //Set authors
        $authorList = array();
        foreach($postData->authors as $author) {
            $authorList[] = new Model\DocumentAuthor($author->name);
        }
        $doc->setAuthors($authorList);

        //Set Abstract
        $sections = array();
        foreach($postData->abstract as $sec) {
            $sections[] = new Model\DocumentSection($sec->content, $sec->type);
        }
        $doc->setAbstract(new Model\DocumentAbstract($sections));

        //Set Content
        $sections = array();
        foreach($postData->content as $sec) {
            $sections[] = new Model\DocumentSection($sec->content, $sec->type);
        }
        $doc->setContent(new Model\DocumentContent($sections));

        //Set Citations
        $citationList = array();
        foreach($postData->citations as $cite) {
            $citationList[] = new Model\DocumentCitation($cite->content);
        }
        $doc->setCitations($citationList);

        return $doc;
    }
}

/* EOF: DocumentAPIHandler.php */