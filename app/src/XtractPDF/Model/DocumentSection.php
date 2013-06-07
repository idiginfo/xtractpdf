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
     * @ODM\Collection
     */
    protected $paragraphs;

    // --------------------------------------------------------------

    public function __construct($title, array $paragraphs = array())
    {
        $this->title      = $title;
        $this->paragraphs = $paragraphs;
    }

    // --------------------------------------------------------------

    public function setTitle($title)
    {
        $this->title = $title;
    }

    // --------------------------------------------------------------

    public function setParagraphContent($pos, $content)
    {
        $this->paragraphs[$pos] = $content;
    }

    // --------------------------------------------------------------

    public function addParagraph($content, $after = null)
    {
        $this->paragraphs[] = $content;
    }    
}

/* EOF: DocumentSection.php */