<?php

namespace XtractPDF\Library;

use Doctrine\ODM\MongoDB\DocumentManager;
use XtractPDF\Model\Document as DocumentModel;
use XtractPDF\PdfDataHandler\PdfDataHandlerInterface;
use XtractPDF\Helper\ArrayDifferator;
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
     * @param Doctrine\ODM\MongoDB\DocumentManager $dm
     * @param XtractPDF\PdfDataHandler\PdfDataHandlerInterface $dataHandler
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

        //Persist it
        $this->dataHandler->save($uniqId, $streamId);
        $doc = new DocumentModel($uniqId);
        $this->updateDocument($doc);

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

        //Persist it
        $this->dataHandler->save($document->uniqId, $streamId);
        $this->updateDocument($document);

        //Log it
        $this->log('create', $document);

        //Return the saved version
        return $document;
    }

    // --------------------------------------------------------------

    /**
     * Update Document
     *
     * @param XtractPDF\Model\Document $document  Document to update
     * @param array                    $diff      Optional diff from old document to log
     * @param boolean                  $flush     Flush the change instantly? (default = true)
     */
    public function updateDocument(DocumentModel $document, array $diff = array(), $flush = true)
    {
        $document->markModified();
        $this->dm->persist($document);

        if ($flush) {
            $this->dm->flush();
        }

        //Log it
        $this->log('update', $doc, $diff);
    }

    // --------------------------------------------------------------

    public function checkDocumentExists($uniqId)
    {
        return (boolean) $this->getDocument($uniqId);
    }

    // --------------------------------------------------------------

    public function getDocument($uniqId)
    {
        return $this->dm
            ->getRepository(self::DOC_CLASSNAME)
            ->findOneBy(array('uniqId' => $uniqId));
    }

    // --------------------------------------------------------------

    public function removeDocument(DocumentModel $document, $flush = true)
    {
        //Delete the data from the stream
        $this->dataHandler->del($document->uniqId);

        //Delete the document record
        $this->dm->remove($document);

        //Log it
        $this->log('remove', $doc);

        if ($flush) {
            $this->dm->flush();    
        }
    }

    // --------------------------------------------------------------

    public function streamPdf($uniqId)
    {  
        $obj =& $this;
        $streamer = function() use ($uniqId, $obj) {
            $obj->dataHandler->stream($uniqId);
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
        //QB
        $qb = $this->dm->createQueryBuilder(self::DOC_CLASSNAME);

        //Sort
        $qb->sort('isComplete', self::ASC);
        $qb->sort('modified', self::DESC);

        //Limit
        if ($limit) {
            $qb->limit((int) $limit);
        }

        //Search Query
        if ($query) {
            $qb->field('title')->equals(new MongoRegex("/.*(" . $query . ").*/i"));
        }

        //Run and return result
        return $qb->getQuery()->execute();
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
     * @param array $diff
     */
    protected function log($action, DocumentModel $doc, $diff = array())
    {
        if ($this->auditLogger) {
            $this->auditLogger->log($action, $doc, $diff);
        }
    }
}

/* EOF: DocumentMgr.php */