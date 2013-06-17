<?php

namespace XtractPDF\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use XtractPDF\Core\Model as BaseModel;
use InvalidArgumentException, ReflectionProperty;

/**
 * Document Bibliographic Metadata
 * @ODM\EmbeddedDocument 
 */
class DocumentBiblioMeta extends BaseModel
{
    /**
     * @var string
     * @ODM\String
     *
     * \@dispName        Title
     * \@dispPlaceholder e.g. Some Long-winded Title of a Journal Article     
     */
    protected $title;

    /**
     * @var string     
     * @ODM\String
     *
     * \@dispName        DOI
     * \@dispPlaceholder e.g. 10.1000/182
     */
    protected $doi;

    /**
     * @var string
     * @ODM\String
     *
     * \@dispName        Pubmed ID
     * \@dispPlaceholder e.g. 234203823
     */
    protected $pmid;

    /**
     * @var string
     * @ODM\String
     *
     * \@dispName        ISBN
     * \@dispPlaceholder e.g. 12345-6789X
     */
    protected $isbn;

    /**
     * @var string
     * @ODM\String
     *
     * \@dispName        Journal
     * \@dispPlaceholder e.g. Some Journal Title
     */
    protected $journal;

    /**
     * @var string
     * @ODM\String
     *
     * \@dispName        ISSN
     * \@dispPlaceholder e.g. 12345-6789X
     */
    protected $issn;

    /**
     * @var string
     * @ODM\String
     *
     * \@dispName        Volume
     * \@dispPlaceholder e.g. 12
     */
    protected $volume;

    /**
     * @var string
     * @ODM\String
     *
     * \@dispName        Issue
     * \@dispPlaceholder e.g. 6
     */
    protected $issue;

    /**
     * @var int
     * @ODM\Int
     *
     * \@dispName        Start Page
     * \@dispPlaceholder e.g. 234
     */
    protected $startPage;

    /**
     * @var int
     * @ODM\Int
     *
     * \@dispName        End Page
     * \@dispPlaceholder e.g. 238
     */
    protected $endPage;

    /**
     * @var int
     * @ODM\Int
     *
     * \@dispName        Year
     * \@dispPlaceholder e.g. 2003
     */
    protected $year;

    /**
     * @var string
     * @ODM\Collection
     *
     * \@dispName        Keywords
     * \@dispPlaceholder e.g. comma, or; semicolon, separated, list; of, keywords
     */
    protected $keywords;

    // --------------------------------------------------------------

    public function __construct()
    {
        $this->keywords = array();
    }

    // --------------------------------------------------------------

    public static function getDispPlaceholders()
    {
        $arr = array();
        foreach (self::getDispInfo() as $k => $v) {
            $arr[$k] = $v['dispPlaceholder'];
        }
        return $arr;
    }

    // --------------------------------------------------------------

    public static function getDispNames()
    {
        $arr = array();
        foreach (self::getDispInfo() as $k => $v) {
            $arr[$k] = $v['dispName'];
        }
        return $arr;
    }

    // --------------------------------------------------------------

    /**
     * Get display info for all public properties
     *
     * Returns an array that looks like this:
     * * propName => [dispName => 'Display Name', dispPlaceholder => 'e.g. Some Example']
     * 
     *
     * @return array  Multidimensional array
     */
    public static function getDispInfo()
    {
        $arr = array();

        foreach (self::getPublicProperties() as $k) {

            $subArr = array('dispName' => null, 'dispPlaceholder' => null);

            $prop = new ReflectionProperty(get_called_class(), $k);
            $docComment = $prop->getDocComment();

            if (preg_match("/@dispName\s+(.+?)[\n|\r\n]/", $docComment, $matches)) {
                $subArr['dispName'] = $matches[1];
            }

            if (preg_match("/@dispPlaceholder\s+(.+?)[\n|\r\n]/", $docComment, $matches)) {
                $subArr['dispPlaceholder'] = $matches[1];
            }

            $arr[$k] = $subArr;
        }

        return $arr;
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