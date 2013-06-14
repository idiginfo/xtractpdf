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
     * @var string
     * @ODM\String
     */
    protected $issn;

    /**
     * @var string
     * @ODM\String
     */
    protected $volume;

    /**
     * @var string
     * @ODM\String
     */
    protected $issue;

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
     * @var string
     * @ODM\Collection
     */
    protected $keywords;

    // --------------------------------------------------------------

    public function __construct()
    {
        $this->keywords = array();
    }

    // --------------------------------------------------------------

    public function __set($item, $val)
    {
        if (in_array($item, array_keys(get_object_vars($this)))) {

            //Basic Validations
            switch($item) {
                case 'authors':
                    throw new InvalidArgumentException("Use " . get_called_class() . "::setAuthors() or " . get_called_class() . "::addAuthor() to set authors");
                break;
                case 'keywords':
                    if ( ! is_array($keywords)) {
                        throw new InvalidArgumentException( sprintf("%s::keywords must be an array!", get_called_class() ));
                    }
                break;
            }

            //Set it
            $this->$item = $val;
        }
    }

}

/* EOF: DocumentBiblioMeta.php */