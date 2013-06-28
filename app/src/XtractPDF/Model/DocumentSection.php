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
 * Document Section
 * @ODM\EmbeddedDocument 
 */
class DocumentSection extends BaseModel
{
    /**
     * @var array
     */
    protected static $_allowedTypes = array(
        'paragraph'  => 'Paragraph',
        'heading'    => 'Heading',
        'subheading' => 'Subheading'
    );
    
    // --------------------------------------------------------------

    /**
     * @var string
     * @ODM\String
     */    
    protected $content;

    /**
     * @var string
     * @ODM\String
     */    
    protected $type;

    // --------------------------------------------------------------

    public function __construct($content, $type = 'paragraph')
    {
        $this->setContent($content);
        $this->setType($type);
    }

    // --------------------------------------------------------------

    /**
     * Get valid section type names
     *
     * @return array  Keys are typenames, values are display-friendly versions
     */
    public static function getAllowedTypes()
    {
        return self::$_allowedTypes;
    }

    // --------------------------------------------------------------

    public function __get($item)
    {
        switch ($item) {
            case 'typeDis:':
                return $this->typeDisp();
            case 'allowedTypes':
                return self::getAllowedTypes();
            default:
                return parent::__get($item);
        }
    }

    // --------------------------------------------------------------

    public function setType($type)
    {
        if ( ! isset(self::$_allowedTypes[$type])) {
            throw new InvalidArgumentException(sprintf(
                "%s is invalid type; allowed types are %s",
                $type,
                implode(', ', array_keys(self::$_allowedTypes))
            ));
        }

        $this->type = $type;
    }

    // --------------------------------------------------------------

    public function setContent($content)
    {
        $this->content = $content;
    }

    // --------------------------------------------------------------

    public function typeDisp()
    {
        return self::$_allowedTypes[$this->type];
    }

    // --------------------------------------------------------------

    public function __tostring()
    {
        return $this->content;
    }    
}


/* EOF: DocumentParagraph.php */