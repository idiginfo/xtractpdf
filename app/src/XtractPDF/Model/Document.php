<?php

namespace XtractPDF\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use XtractPDF\Core\Model as BaseModel;
use DateTime;

/**
 * Document
 * @ODM\Document
 */
class Document extends BaseModel
{
    /** 
     * @ODM\Id
     */
    protected $id;

    /**
     * @var string
     * @ODM\String
     * @ODM\UniqueIndex
     */
    protected $uniqId;

    /**
     * @var DateTime
     * @ODM\Date
     */
    protected $created;

    /**
     * @var DateTime
     * @ODM\Date
     */
    protected $extracted;

    /**
     * @var boolean
     * @ODM\Boolean
     */
    protected $isExtracted;

    /**
     * @var boolean
     * @ODM\Boolean
     */
    protected $isComplete;

    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="DocumentSection")
     */
    protected $sections;

    /**
     * @var array
     * @ODM\Collection
     */
    protected $citatations;

    /**
     * @var XtractPDF\Model\DcoumentBiblioMeta
     * @ODM\EmbedOne(targetDocument="DocumentBiblioMeta")
     */
    protected $biblioMeta;

    // --------------------------------------------------------------

    /**
     * Constructor
     *
     * @param string $filename  Relative filename
     * @param string $md5       An identifier that uniquely identifies PDF content
     */
    public function __construct($uniqId)
    {
        $this->uniqId      = $uniqId;
        $this->isExtracted = false;
        $this->isComplete  = false;
        $this->biblioMeta  = new DocumentBiblioMeta();
        $this->created     = new DateTime();
        $this->citations   = array();
        $this->sections    = array();
    }


    // --------------------------------------------------------------

    public function markExtracted()
    {
        $this->isExtracted = true;
    }

    // --------------------------------------------------------------

    public function markComplete($isComplete)
    {
        $this->isComplete = (boolean) $isComplete;
    }

    // --------------------------------------------------------------

    public function addNewSection($title, array $paragraphs)
    {
        $this->addSection(new DocumentSection($title, $paragraphs));
    }

    // --------------------------------------------------------------

    public function addSection(DocumentSection $section, $pos = null)
    {
        if ($pos) {
            $this->sections[$pos] = $section;    
        }
        else {
            $this->sections[] = $section;
        }
    }

    // --------------------------------------------------------------

    public function addCitation($citation)
    {
        $this->citations[] = $citation;
    }

    // --------------------------------------------------------------

    public function getMeta($name = null)
    {
        return ($name) ? $this->biblioMeta->$name : $this->biblioMeta;
    }

    // --------------------------------------------------------------

    public function setMeta($name, $value)
    {
        $this->biblioMeta->$name = $value;
    }

}

/* EOF: Document.php */