<?php

namespace XtractPDF\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use XtractPDF\Core\Model as BaseModel;
use DateTime;

/**
 * Audit Log Entry
 * @ODM\Document
 */
class AuditLogEntry extends BaseModel
{
    /** 
     * @ODM\Id
     */
    protected $id;

    /**
     * @var XtractPDF\Model\Document
     * @ODM\ReferenceOne(targetDocument="Document")
     */
    protected $document;

    /**
     * @var DateTime
     * @ODM\Date
     */
    protected $timestamp;

    /**
     * @var string
     * @ODM\String
     */
    protected $actionName;

    /**
     * @var string  JSON-encoded diff
     * @ODM\String
     */
    protected $diff;

    /**
     * @var array
     * @ODM\Hash
     */
    protected $context;

    // --------------------------------------------------------------

    public function __construct($actionName, Document $doc, $diff = null, $context = array())
    {
        $this->timestamp  = new DateTime();
        $this->document   = $doc;
        $this->actionName = $actionName;
        $this->diff       = (is_string($diff)) ? $diff : json_encode($diff);
        $this->context    = $context;
    }
}

/* EOF: AuditLogEntry.php */