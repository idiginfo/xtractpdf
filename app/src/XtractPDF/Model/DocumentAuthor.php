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
 * Document Author
 * @ODM\EmbeddedDocument 
 */
class DocumentAuthor extends BaseModel
{
    /**
     * @var string
     * @ODM\String
     */
    protected $name;

    // --------------------------------------------------------------

    public function __construct($authorName)
    {
        $this->name = $authorName;
    }

    // --------------------------------------------------------------

    public function __toString()
    {
        return $this->name;
    }
}

/* EOF: DocumentAuthor.php */