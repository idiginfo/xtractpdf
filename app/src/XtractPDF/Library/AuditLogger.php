<?php

namespace XtractPDF\Library;

use XtractPDF\Model\AuditLogEntry;
use XtractPDF\Model\Document as DocumentModel;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Audit Logger
 *
 * @TODO: possibly add an API for retrieving log entries
 */
class AuditLogger
{
    /**
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    /**
     * @var array
     */
    private $context;

    // --------------------------------------------------------------

    /**
     * Constructor
     *
     * @param Doctrine\ODM\MongoDB\DocumentManager $dm
     * @param array $context  Contextual information to be stored with log entries
     */
    public function __construct(DocumentManager $dm, array $context = array())
    {
        $this->dm = $dm;
        $this->setContext($context);
    }

    // --------------------------------------------------------------

    /**
     * Set Contextual information to be stored with log entries
     *
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->context = $context;
    }

    // --------------------------------------------------------------

    /**
     * Create an audit log entry
     *
     * @param string                   $actionName  Name of action performed
     * @param XtractPDf\Model\Document $doc         Document worked on
     * @param array                    $diff        Optional diff of documents
     */
    public function log($actionName, DocumentModel $doc, array $diff = array())
    {
        $entry = new AuditLogEntry($actionName, $doc, $diff, $this->context);
        $this->dm->persist($entry);
        $this->dm->flush();
    }
}

/* EOF: AuditLogger.php */