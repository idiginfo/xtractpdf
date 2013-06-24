<?php

namespace XtractPDF\Library;

use Doctrine\ODM\MongoDB\DocumentManager;
use XtractPDF\Model\Document as DocumentModel;
use XtractPDF\PdfDataHandler\PdfDataHandlerInterface;
use MongoRegex;

/**
 * Manager class for interacting with Document Models (persisting, fetching, etc)
 */
class DocumentMgr
{
    const DOC_CLASSNAME = '\XtractPDF\Model\Document';

    const ASC  = 'asc';
    const DESC = 'desc';

    // --------------------------------------------------------------

    /**
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    /**
     * @var XtractPDF\PdfDataHandler\PdfDataHandlerInterface
     */
    private $dataHandler;

    /**
     * @var XtractPDF\Library\AuditLogger 
     */
    private $auditLogger;

    // --------------------------------------------------------------

    /**
     * Constructor
     *
     * @param Doctrine\ODM\MongoDB\DocumentManager             $dm
     * @param XtractPDF\PdfDataHandler\PdfDataHandlerInterface $dataHandler
     * @param XtractPDF\Library\AuditLogger                    $auditLogger
     */
    public function __construct(DocumentManager $dm, PdfDataHandlerInterface $dataHandler, AuditLogger $auditLogger = null)
    {
        $this->dm          = $dm;
        $this->dataHandler = $dataHandler;

        if ($auditLogger) {
            $this->setAuditLogger($auditLogger);
        }
    }

    // --------------------------------------------------------------

    /**
     * Set an optional audit logger
     *
     * @param XtractPDF\Library\AuditLogger $auditLogger
     */
    public function setAuditLogger(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    // --------------------------------------------------------------

    /**
     * Create a new document
     *
     * @param string  $uniqId    Unique ID of the PDF; if the same PDF is uploaded twice, it should be the same
     * @param string  $streamId  Stream location to be sent to fopen('', 'r')
     * @return XtractPDF\Model\Document|false
     */
    public function createDocument($uniqId, $streamId)
    {
        //If the unique ID already exists in the database, return false
        if ($this->checkDocumentExists($uniqId)) {
            return false;
        }

        //Save the PDF
        $this->dataHandler->save($uniqId, $streamId);

        //Create and save a new Document Model
        $doc = new DocumentModel($uniqId);
        $this->saveDocument($doc);

        //Log it
        $this->log('create', $doc);

        //Return it
        return $doc;
    }

    // --------------------------------------------------------------

    /**
     * Save a new document
     *
     * Same as create document, but assumes that a DocumentModel has already been created
     *
     * @param XtractPDF\Model\Document  $document  Document Object
     * @param string                    $streamId  Stream location to be sent to fopen('', 'r')
     * @return XtractPDF\Model\Document|false     
     */
    public function saveNewDocument(DocumentModel $document, $streamId)
    {
        //If the unique ID already exists in the database, return false
        if ($this->checkDocumentExists($document->uniqId)) {
            return false;
        }

        //Save the PDF
        $this->dataHandler->save($document->uniqId, $streamId);

        //Save the document model
        $this->saveDocument($document);

        //Log it
        $this->log('create', $document);

        //Return the saved version
        return $document;
    }

    // --------------------------------------------------------------

    /**
     * Update Document
     *
     * @param XtractPDF\Model\Document $doc    Document to update
     * @param array                    $diff   Diff of document for logging purposes
     * @param boolean                  $flush  Flush the change instantly? (default = true)
     */
    public function updateDocument(DocumentModel $doc, array $diff = array(), $flush = true)
    {
        //Save the document
        $this->saveDocument($doc, $flush);

        //Log it
        $this->log('update', $doc, array('diff' => $diff));

    }

    // --------------------------------------------------------------

    /**
     * Check if a document uniqId exists
     *
     * @param string $uniqId
     * @return boolean
     */
    public function checkDocumentExists($uniqId)
    {
        return (boolean) $this->getDocument($uniqId);
    }

    // --------------------------------------------------------------

    /**
     * Retrieve a single document by its uniqId
     *
     * Returns null if document not found
     *
     * @param string $uniqId
     * @return XtractPDF\Model\Document|null
     */
    public function getDocument($uniqId)
    {
        return $this->dm
            ->getRepository(self::DOC_CLASSNAME)
            ->findOneBy(array('uniqId' => $uniqId));
    }

    // --------------------------------------------------------------

    /**
     * Remove a document
     *
     * @param XtractPDF\Model\Document $document
     * @param boolean $flush
     */
    public function removeDocument(DocumentModel $document, $flush = true)
    {
        //Delete the data from the stream
        $this->dataHandler->del($document->uniqId);

        //Delete the document record
        $this->dm->remove($document);

        //Log it
        $this->log('remove', $document);

        if ($flush) {
            $this->dm->flush();    
        }
    }

    // --------------------------------------------------------------

    public function getStreamPdfUri($uniqId)
    {
        return $this->dataHandler->streamuri($uniqId);
    }

    // --------------------------------------------------------------

    /**
     * Get URI to stream PDF data for a document
     *
     * @param string   $uniqId
     * @return string  Path to stream
     */
    public function streamPdf($uniqId)
    {  
        $dataHandler =& $this->dataHandler;

        $streamer = function() use ($uniqId, $dataHandler) {
            $dataHandler->stream($uniqId);
        };

        return $streamer;
    }

    // --------------------------------------------------------------

    /**
     * List Documents
     *
     * @param int    $limit   Optional limit
     * @param string $query   Optional query string
     * @param int    $offset  Optional offset
     * @return Doctrine\ODM\MongoDB\Cursor
     */
    public function listDocuments($limit = null, $query = null, $offset = null)
    {
        //Run and return result
        return $this->getListDocumentsQueryBuilder($limit, $query, $offset)->getQuery()->execute();
    }    

    // --------------------------------------------------------------

    /**
     * Get Query Builder for list documents action
     *
     * @param int    $limit   Optional limit
     * @param string $query   Optional query string
     * @param int    $offset  Optional offset
     * @return Doctrine\ODM\MongoDB\QueryBuilder
     */
    protected function getListDocumentsQueryBuilder($limit = null, $query = null, $offset = null)
    {
        //QB
        $qb = $this->dm->createQueryBuilder(self::DOC_CLASSNAME);

        //@TODO: Re-enable the dynamic sort - Disabled per Kelsie
        // $qb->sort('isComplete', self::ASC);
        // $qb->sort('modified', self::DESC);
        $qb->sort('uniqId', self::ASC);

        //Limit
        if ($limit) {
            $qb->limit((int) $limit);
        }

        //Search Query
        if ($query) {
            $qb->field('title')->equals(new MongoRegex("/.*(" . $query . ").*/i"));
        }        

        return $qb;
    }

    // --------------------------------------------------------------

    /**
     * Save a document
     *
     * @param XtractPDF\Model\Document $doc
     * @param boolean $flush
     */
    protected function saveDocument(DocumentModel $doc, $flush = true)
    {
        $doc->markModified();
        $this->dm->persist($doc);

        if ($flush) {
            $this->flush();
        }
    }

    // --------------------------------------------------------------

    /**
     * Only useful if you performed a bunch of operations without the 'flush' parameter
     */
    public function flush()
    {
        $this->dm->flush();
    }    

    // --------------------------------------------------------------

    /**
     * Log changes to the document
     *
     * @param string $action
     * @param XtractPDF\Model\Document $doc
     * @param array                    $extraData  Optional extra data
     */
    protected function log($action, DocumentModel $doc, array $extraData = array())
    {
        if ($this->auditLogger) {
            $this->auditLogger->log($action, $doc, $extraData);
        }
    }
}

/* EOF: DocumentMgr.php */