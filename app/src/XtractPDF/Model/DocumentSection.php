<?php

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
    protected static $allowedTypes = array(
        'paragraph'  => 'Paragraph',
        'heading'    => 'Heading',
        'subheading' => 'Subheading'
    );

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

    public function __get($item)
    {
        if ($item == 'typeDisp') {
            return $this->typeDisp();
        }
        else {
            return parent::__get($item);
        }
    }

    // --------------------------------------------------------------

    public function setType($type)
    {
        if ( ! isset(self::$allowedTypes[$type])) {
            throw new InvalidArgumentException(sprintf(
                "%s is invalid type; allowed types are %s"),
                $type,
                implode(', ', array_keys(self::$allowedTypes)
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

    /**
     * Return array
     */
    public function getAllowedTypes()
    {
        return self::$allowedTypes;
    }

    // --------------------------------------------------------------

    public function typeDisp()
    {
        return self::$allowedTypes[$this->type];
    }

    // --------------------------------------------------------------

    public function __tostring()
    {
        return $this->content;
    }    
}


/* EOF: DocumentParagraph.php */