<?php

namespace XtractPDF\Model;

use XtractPDF\Library\Model as BaseModel;
use InvalidArgumentException;

/**
 * Document Bibliographic Metadata
 */
class DocumentBiblioMeta extends BaseModel
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $doi;

    /**
     * @var string
     */
    protected $abstract;

    /**
     * @var string
     */
    protected $pmid;

    /**
     * @var string
     */
    protected $isbn;

    /**
     * @var string
     */
    protected $journal;

    /**
     * @var int
     */
    protected $startPage;

    /**
     * @var int
     */
    protected $endPage;

    /**
     * @var int
     */
    protected $year;

    /**
     * @var array
     */
    protected $authors;

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
                    throw new InvalidArgumentException("Use " . get_called_class() . "::setAuthor() or " . get_called_class() . "::addAuthor() to set authors");
                break;
            }

            //Set it
            $this->$item = $val;
        }
    }

    // --------------------------------------------------------------

    public function setAuthor($pos, $name)
    {
        $this->authors[$pos] = $name;
    }    

    // --------------------------------------------------------------

    public function addAuthor($name)
    {
        $this->authors[] = $name;
    }
}

/* EOF: DocumentBiblioMeta.php */