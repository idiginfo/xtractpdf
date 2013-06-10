<?php

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

/* EOF: DocumentCitation.php */