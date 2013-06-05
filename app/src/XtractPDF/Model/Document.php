<?php

namespace XtractPDF\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use XtractPDF\Library\Model as BaseModel;
use DateTime;

/**
 * Document
 * @ODM\Document
 */
class Document extends BaseModel
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $md5;

    /**
     * @var string
     */
    protected $filename;

    /**
     *  @var DateTime
     */
    protected $created;

    /**
     *  @var DateTime
     */
    protected $extracted;

    /**
     * @var array
     */
    protected $sections;

    /**
     * @var array
     */
    protected $citatations;

    /**
     * @var XtractPDF\Model\DcoumentBiblioMeta
     */
    protected $biblioMeta;

    // --------------------------------------------------------------

    public function __construct($filename, $md5)
    {
        $this->filename   = $filename;
        $this->md5        = $md5;
        $this->biblioMeta = new DocumentBiblioMeta();
        $this->created    = new DateTime();
        $this->citations  = array();
        $this->sections   = array();
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