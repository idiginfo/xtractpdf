<?php

/**
 * XtractPDF - A PDF Content Extraction and Curation Tool
 *
 * This program is free software under the GNU General Public License (v2)
 * See LICENSE.md for a complete copy of the license
 *
 * @package     XtractPDF
 * @author      Florida State University iDigInfo (http://idiginfo.org)
 * @copyright   Copyright (C) 2013 Florida State University (http://fsu.edu)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt
 * @link        http://idiginfo.org
 */

// ------------------------------------------------------------------

namespace XtractPDF\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use XtractPDF\Core\Model as BaseModel;

/**
 * Topic
 * @ODM\Document
 */
class Topic extends BaseModel
{
    /** 
     * @ODM\Id
     */
    protected $id;

    /**
     * @ODM\String
     * @ODM\UniqueIndex     
     * @var string     
     */
    protected $mainTerm;

    /**
     * @ODM\Collection
     * @var array
     */
    protected $terms;

    // --------------------------------------------------------------

    public function __construct($mainTerm, array $terms = array())
    {        
        $this->setTerms($terms);
        $this->setMainTerm($mainTerm);        
    }

    // --------------------------------------------------------------

    public function setMainTerm($mainTerm)
    {
        $this->setTerm($mainTerm);
        $this->mainTerm = $this->normalizeTerm($mainTerm);
    }

    // --------------------------------------------------------------

    public function setTerms(array $terms)
    {
        $this->terms = array();
        foreach($terms as $term) {
            $this->setTerm($term);
        }
    }

    // --------------------------------------------------------------

    public function setTerm($term)
    {
        $normalized = $this->normalizeTerm($term);

        if ( ! in_array($normalized, $this->terms)) {
            $this->terms[] = $normalized;    
        }
    }

    // --------------------------------------------------------------

    protected function normalizeTerm($term)
    {
        //Remove non-alphanumerics
        $term = preg_replace("/[^A-Za-z0-9 ]/", '', $term);

        //Transliterate non-utf-8 characters
        $term = iconv('utf-8', 'us-ascii//TRANSLIT', $term);

        //String to lower
        $term = strtolower($term);

        //Return it
        return $term;
    }
}

/* EOF: Topic.php */