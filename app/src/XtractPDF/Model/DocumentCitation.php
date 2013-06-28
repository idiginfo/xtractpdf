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

/**
 * Document Citation
 * @ODM\EmbeddedDocument 
 */
class DocumentCitation extends BaseModel
{
    /**
     * @var string
     * @ODM\String
     */ 
    protected $content;

    // --------------------------------------------------------------

    public function __construct($content)
    {
        $this->uuid    = uniqid();
        $this->content = $content;
    }

    // --------------------------------------------------------------

    public function setContent($content)
    {
        $this->content = $content;
    }

    // --------------------------------------------------------------

    public function __tostring()
    {
        return $this->content;
    }      
}

/* EOF: DocumentCitation.php */