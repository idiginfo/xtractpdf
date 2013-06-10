<?php

namespace XtractPDF\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use XtractPDF\Core\Model as BaseModel;

/**
 * Document Section
 * @ODM\EmbeddedDocument 
 */
class DocumentSection extends BaseModel
{
    /**
     * @var string
     * @ODM\String
     */
    protected $title;

    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="DocumentParagraph")
     */
    protected $paragraphs;

    // --------------------------------------------------------------

    public function __construct($title, array $paragraphs = array())
    {
        $this->title = $title;
        foreach($paragraphs as $paragraph) {

            if ($paragraph instanceOf DocumentParagraph) {
                $this->addParagraph($paragraph);
            }
            else {
                $this->addParagraph(new DocumentParagraph($paragraph));    
            }
        }
    }

    // --------------------------------------------------------------

    public function setTitle($title)
    {
        $this->title = $title;
    }

    // --------------------------------------------------------------

    public function addParagraph(DocumentParagraph $paragraph)
    {
        $this->paragraphs[] = $paragraph;
    }    
}

/* EOF: DocumentSection.php */