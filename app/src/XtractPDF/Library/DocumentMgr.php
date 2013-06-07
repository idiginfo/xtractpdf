<?php

namespace XtractPDF\Library;

use Doctrine\ODM\MongoDB\DocumentManager;
use XtractPDF\Model\Document as DocumentModel;
use XtractPDF\PdfDataHandler\PdfDataHandlerInterface;

/**
 * Manager class for interacting with Document Models (persisting, fetching, etc)
 */
class DocumentMgr
{
    const DOC_CLASSNAME = '\XtractPDF\Model\Document';

    const ASC  = 1;
    const DESC = -1;

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

    public function updateDocument(DocumentModel $document, $flush = true)
    {
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

    public function removeDocument($uniqId, $flush = true)
    {
        //Delete the data from the stream
        $this->dataHandler->del($uniqId);

        //Delete the document record
        $doc = $this->getDocument($uniqId);
        $this->dm->remove($doc);

        if ($flush) {
            $this->dm->flush();    
        }
    }

    // --------------------------------------------------------------

    public function streamPdf($uniqId)
    {  
        $streamer = function() use ($uniqId) {
            $this->dataHandler->stream($uniqId);
        };

        return $streamer;
    }

    // --------------------------------------------------------------

    /**
     * List Documents
     *
     * @param int    $limit
     * @param string $sortField
     * @param int    $sortDir
     * @return Doctrine\ODM\MongoDB\Cursor
     */
    public function listDocuments($limit = null, $sortField = 'created', $sortDir = self::DESC)
    {
        //Set sort
        $sort = ($sortField)
            ? array($sortField, $sortDir)
            : array();

        return $this->dm->getRepository(self::DOC_CLASSNAME)->findBy(array(), $sort, (int) $limit);
    }    
}

/* EOF: DocumentMgr.php */