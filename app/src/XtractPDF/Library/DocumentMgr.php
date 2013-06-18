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

    // --------------------------------------------------------------

    /**
     * Constructor
     *
     * @param Doctrine\ODM\MongoDB\DocumentManager $dm
     * @param XtractPDF\PdfDataHandler\PdfDataHandlerInterface $dataHandler
     */
    public function __construct(DocumentManager $dm, PdfDataHandlerInterface $dataHandler)
    {
        $this->dm          = $dm;
        $this->dataHandler = $dataHandler;
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

        //Return it
        return $doc;
    }

    // --------------------------------------------------------------

    /**
     * Save a new document - Same as create document, but does not dispense a new document object
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
        $this->updateDocument($doc);

        //Return the saved version
        return $doc;
    }

    // --------------------------------------------------------------

    public function updateDocument(DocumentModel $document, $flush = true)
    {
        $document->markModified();
        $this->dm->persist($document);

        if ($flush) {
            $this->dm->flush();
        }
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
}

/* EOF: DocumentMgr.php */