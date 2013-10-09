<?php

namespace XtractPDF\Library

use XtractPDF\Model\Document;
use XtractPDF\Model\Topic;
use Traversable;

/**
 * Index Documents in SOLR
 */
class DocumentIndex
{
    /**
     * Return SOLR config XML string
     *
     * @return string
     */
    public function getSolrConfig()
    {

    }

    /**
     * Index Documents
     *
     * @param Traversable $documents
     */
    public function indexDocuments(Traversable $documents)
    {

    }

    /**
     * Index a single document
     */
    public function indexDocument(Document $doc)
    {

    }

    /**
     * Search the index for topical terms (OR query)
     */
    public function searchOnTopic(Topic $topic)
    {

    }

    /**
     * Do a generic query
     */
    public function doQuery($query)
    {

    }

}

/* EOF: DocumentIndexer.php */