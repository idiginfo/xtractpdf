<?php

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
            $this->addSection($section);
        }
    }

    // --------------------------------------------------------------

    public function addSection(DocumentSection $section)
    {
        $this->sections[] = $section;
    }    
}

/* EOF: DocumentContent.php */