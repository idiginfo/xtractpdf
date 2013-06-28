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
use InvalidArgumentException;

/**
 * Document Bibliographic Metadata
 * @ODM\EmbeddedDocument 
 */
class DocumentContent extends BaseModel
{
    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="DocumentSection")
     */
    protected $sections;

    // --------------------------------------------------------------

    public function __construct(array $sections = array())
    {
        $this->setSections($sections);
    }

    // --------------------------------------------------------------

    public function setSections(array $sections = array())
    {
        //Empty out existing sections
        $this->sections = array();

        //Add everything
        foreach($sections as $sec) {
            $this->addSection($sec);
        }
    }

    // --------------------------------------------------------------

    public function addSection(DocumentSection $section)
    {
        $this->sections[] = $section;
    }    
}

/* EOF: DocumentContent.php */