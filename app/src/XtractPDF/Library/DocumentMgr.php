<?php

//TODO DO WE REALLY NEED THIS?  Probably a good idea

class DocumentMgr
{
    /**
     *
     */
    private $dm;

    /**
     * Constructor
     *
     * @param MONGODOM\DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    //This method can contain the logic for ensuring that we don't overwrite
    public function persistDocument(DocumentModel $document);

    public function retrieveDocument($id);

    public function retrieveDocumentByMd5($md5);
}

/* EOF: DocumentMgr.php */