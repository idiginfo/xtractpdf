<?php
  
/**
 * XtractPDF - A PDF Content Extraction and Curation Tool
 *
 * This program is free software under the GNU General Public License (v2)
 * See LICENSE.md for a complete copy of the license
 *
 * @package     XtractPDF
 * @author      Florida State University iDigInfo (http://idiginfo.org)
 * @copyright   Copyright (C) 2013 Florida State University (http://fsu.edu)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt
 * @link        http://idiginfo.org
 */

// ------------------------------------------------------------------

namespace XtractPDF\Library;

use Doctrine\ODM\MongoDB\DocumentManager;
use XtractPDF\Model\Document as DocumentModel;
use XtractPDF\PdfDataHandler\PdfDataHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use MongoRegex;

class SessionLimitedDocumentMgr extends DocumentMgr
{
    /**
     * @var Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;

    // --------------------------------------------------------------
    
    /**
     * Constructor
     *
     * @param Doctrine\ODM\MongoDB\DocumentManager              $dm
     * @param XtractPDF\PdfDataHandler\PdfDataHandlerInterface  $dataHandler
     * @param XtractPDF\Library\AuditLogger                     $auditLogger
     * @param Symfony\Component\HttpFoundation\Session\Session  $session
     */
    public function __construct(DocumentManager $dm, PdfDataHandlerInterface $dataHandler, AuditLogger $auditLogger = null, Session $session = null)
    {
        parent::__construct($dm, $dataHandler, $auditLogger);

        if ( ! $session) {
            throw new LogicException("Session-aware Document Manager requires session parameter!");
        }

        //Set the session
        $this->session = $session;
        if ( ! $this->session->has('xtract-docs')) {
            $this->session->set('xtract-docs', array());
        } 
    }

    // --------------------------------------------------------------

    /**
     * Get Document
     */
    public function getDocument($uniqId)
    {
        if (in_array($uniqId, $this->session->get('xtract-docs'))) {
            return parent::getDocument($uniqId);
        }
        else {
            return null;
        }
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
        //Save it
        parent::saveDocument($doc, $flush);

        //Add it to the session, if not already in there
        $sessionDocs = $this->session->get('xtract-docs');
        if ( ! in_array($doc->uniqId, $sessionDocs)) {
            $sessionDocs[] = $doc->uniqId;    
        }
        $this->session->set('xtract-docs', $sessionDocs);
    }

    // --------------------------------------------------------------

    public function removeDocument(DocumentModel $document, $flush = true)
    {
        $uid = $document->uniqId;
        parent::removeDocument($document, $flush);

        $sessionDocs = $this->session->get('xtract-docs');
        unset($sessionDocs[array_search($uid)]);
        $this->session->set('xtract-docs', array_values($sessionDocs));
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
    protected function getListDocumentsQueryBuilder($limit = null, $query = null, $offset = null)
    {
        $qb = parent::getListDocumentsQueryBuilder($limit, $query, $offset);
        $qb->field('uniqId')->in($this->session->get('xtract-docs'));
        return $qb;
    }
}

/* EOF: SessionLimitedDocumentMgr.php */