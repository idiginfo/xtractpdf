<?php

namespace XtractPDF\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use XtractPDF\Core\Model as BaseModel;
use InvalidArgumentException;

/**
 * Document Bibliographic Metadata
 * @ODM\EmbeddedDocument 
 */
class DocumentBiblioMeta extends BaseModel
{
    /**
     * @var string
     * @ODM\String
     */
    protected $title;

    /**
     * @var string
     * @ODM\String
     */
    protected $doi;

    /**
     * @var string
     * @ODM\String
     */
    protected $abstract;

    /**
     * @var string
     * @ODM\String
     */
    protected $pmid;

    /**
     * @var string
     * @ODM\String
     */
    protected $isbn;

    /**
     * @var string
     * @ODM\String
     */
    protected $journal;

    /**
     * @var int
     * @ODM\Int
     */
    protected $startPage;

    /**
     * @var int
     * @ODM\Int
     */
    protected $endPage;

    /**
     * @var int
     * @ODM\Int
     */
    protected $year;

    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="DocumentAuthor")
     */
    protected $authors;

    // --------------------------------------------------------------

    public function __construct()
    {
        $this->authors = array();
    }

    // --------------------------------------------------------------

    public function __set($item, $val)
    {
        if (in_array($item, array_keys(get_object_vars($this)))) {

            //Basic Validations
            switch($item) {

                case 'endPage':
                case 'startPage':
                case 'year':
                    if ((string) abs((int) $val) !== (string) $val) {
                        throw new InvalidArgumentException(get_called_class() . "::" . $item . " must be a positive integer");
                    }
                break;
                case 'authors':
                    throw new InvalidArgumentException("Use " . get_called_class() . "::setAuthors() or " . get_called_class() . "::addAuthor() to set authors");
                break;
            }

            //Set it
            $this->$item = $val;
        }
    }

    // --------------------------------------------------------------

    public function setAuthors(array $authors)
    {
        $this->authors = array();

        foreach($authors as $author) {
            $this->addAuthor($author);
        }
    }

    // --------------------------------------------------------------

    public function addAuthor(DocumentAuthor $author)
    {
        $this->authors[] = $author;
    }
}

/* EOF: DocumentBiblioMeta.php */