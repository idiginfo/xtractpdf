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
     * @var array  Any additional data 
     * @ODM\Hash
     */
    protected $extraInfo;

    /**
     * @var array
     * @ODM\Hash
     */
    protected $context;

    // --------------------------------------------------------------

    public function __construct($actionName, Document $doc, array $extraInfo = null, $context = array())
    {
        $this->timestamp  = new DateTime();
        $this->document   = $doc;
        $this->actionName = $actionName;
        $this->extraInfo  = $extraInfo;
        $this->context    = $context;
    }

    // --------------------------------------------------------------

    /** 
     * Function to run when encountering 'diff' field from older version of this document
     * 
     * @ODM\AlsoLoad("diff") 
     */
    public function populateOldDiff($diff)
    {
        $this->extraInfo['diff'] = json_decode($diff, true);
    }


}

/* EOF: AuditLogEntry.php */