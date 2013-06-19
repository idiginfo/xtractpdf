<?php

namespace XtractPDF\Library;

use XtractPDF\Model\AuditLogEntry;
use XtractPDF\Model\Document as DocumentModel;
use XtractPDF\DocRenderer\ArrayRenderer;
use XtractPDF\Helper\ArrayDifferator;
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
     * @var XtractPDF\DocRenderer\ArrayRenderer
     */
    private $arrRender;

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
    public function __construct(DocumentManager $dm, ArrayRenderer $arrRender, array $context = array())
    {
        $this->dm = $dm;
        $this->arrRender = $arrRender;
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
     * @param XtractPDf\Model\Document $doc         Optional representation of previous document state
     */
    public function log($actionName, DocumentModel $doc, DocumentModel $previousDoc = null)
    {
        //Compute diff if there is a previous document representation
        if ($previousDoc) {
            $diff = ArrayDifferator::arrayRecDiff(
                $this->arrRender->render($doc), 
                $this->arrRender->render($previousDoc)
            );

            //Unset the diff modified
            unset($diff['modified']);

            //If there aren't any changes between the last and current doc, ignore the diff
            if (count($diff) == 0) {
                return;
            }
        }
        else {
            $diff = array();
        }

        //Create the entry and save it
        $entry = new AuditLogEntry($actionName, $doc, $diff, $this->context);
        $this->dm->persist($entry);
        $this->dm->flush();
    }
}

/* EOF: AuditLogger.php */