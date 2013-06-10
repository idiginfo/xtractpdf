<?php

namespace XtractPDF\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use XtractPDF\Core\Model as BaseModel;

/**
 * Document Paragraph
 * @ODM\EmbeddedDocument 
 */
class DocumentParagraph
{
    /**
     * @var string
     * @ODM\String
     */    
    protected $uuid;

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
}


/* EOF: DocumentParagraph.php */