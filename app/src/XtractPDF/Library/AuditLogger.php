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

use XtractPDF\Model\AuditLogEntry;
use XtractPDF\Model\Document as DocumentModel;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Events;

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
        //Set Dependencies
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
     * @param array                    $extraData   Optional additional data to add
     */
    public function log($actionName, DocumentModel $doc, array $extraData = array())
    {
        //Create the entry and save it
        $entry = new AuditLogEntry($actionName, $doc, $extraData, $this->context);
        $this->dm->persist($entry);
        $this->dm->flush();
    }
}

/* EOF: AuditLogger.php */