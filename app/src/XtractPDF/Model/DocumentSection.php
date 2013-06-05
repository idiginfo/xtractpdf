<?php

namespace XtractPDF\Model;

use XtractPDF\Library\Model as BaseModel;

/**
 * Document Section
 */
class DocumentSection extends BaseModel
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
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