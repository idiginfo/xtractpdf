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

    public function addParagraph(Paragraph $paragraph, $after = null)
    {
        if ($after && isset($this->paragraphs[$after])) {
            
            $before = array_slice($this->paragraphs, 0, $after + 1);
            $after  = array_slice($this->paragraphs, $after + 1);

            $this->paragraphs = array_merge($before, array($paragraph), $after);
        }
        else {
            $this->paragraphs[] = $paragraph;    
        }
        
    }    
}

/* EOF: DocumentSection.php */