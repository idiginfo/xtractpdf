<?php

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