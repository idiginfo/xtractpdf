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
     * @ODM\EmbedMany(targetDocument="DocumentCitation")
     */
    protected $citations;

    /**
     * @var DocumentAbstract
     * @ODM\EmbedOne(targetDocument="DocumentAbstract")
     */
    protected $abstract;

    /**
     * @var DocumentContent
     * @ODM\EmbedOne(targetDocument="DocumentContent")
     */
    protected $content;

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
        $this->extracted = new DateTime();
        $this->isExtracted = true;
    }

    // --------------------------------------------------------------

    public function markComplete($isComplete)
    {
        $this->isComplete = (boolean) $isComplete;
    }

    // --------------------------------------------------------------

    public function setContent(DocumentContent $content)
    {
        $this->content = $content;
    }

    // --------------------------------------------------------------

    public function setAbstract(DocumentAbstract $abstract)
    {
        $this->abstract = $abstract;
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

    // --------------------------------------------------------------

    public function setAuthors(array $authors)
    {
        $this->biblioMeta->setAuthors($authors);
    }

    // --------------------------------------------------------------

    public function addAuthor(DocumentAuthor $author)
    {
        $this->biblioMeta->addAuthor($author);        
    }

    // --------------------------------------------------------------

    public function setCitations(array $citations)
    {
        $this->citations = array();
        
        foreach($citations as $cite) {
            $this->addCitation($cite);
        }
    }

    // --------------------------------------------------------------

    public function addCitation(DocumentCitation $citation)
    {
        $this->citations[] = $citation;
    }    
}

/* EOF: Document.php */