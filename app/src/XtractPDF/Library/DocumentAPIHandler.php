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

namespace XtractPDF\Library;

use XtractPDF\Model;
use RuntimeException;

/**
 * Build a Document Model from a POST API Request
 *
 * @TODO: Move this?  But where?
 */
class DocumentAPIHandler
{
    /**
     * Build model from POST request
     *
     * @param string $data  Raw JSON from POST request
     * @param XtarctPDF\Model\Docuemnt $doc  Document object to populate
     */
    public function build($data, Model\Document $doc)
    {
        //Decode the data from POST
        $postData = json_decode($data);

        if ( ! $postData) {
            throw new RuntimeException("Could not interpret document data");
        }

        //Set is Complete? (true or false)
        $doc->markComplete($postData->isComplete);

        //Set biblio meta
        foreach($postData->biblioMeta as $bm) {

            if ($bm->name == 'keywords' && is_string($bm->value)) {
                $bm->value = array_filter(array_map('trim', explode(',', $bm->value)));
            }

            $doc->setMeta($bm->name, $bm->value);
        }

        //Set authors
        $authorList = array();
        foreach($postData->authors as $author) {
            if (isset($author->name)) {
                $authorList[] = new Model\DocumentAuthor($author->name);
            }
        }
        $doc->setAuthors($authorList);

        //Set Abstract
        $sections = array();
        foreach($postData->abstract as $sec) {
            if (isset($sec->content)) {
                $sections[] = new Model\DocumentSection($sec->content, $sec->type);
            }
        }
        $doc->setAbstract(new Model\DocumentAbstract($sections));

        //Set Content
        $sections = array();
        foreach($postData->content as $sec) {
            if (isset($sec->content)) {
                $sections[] = new Model\DocumentSection($sec->content, $sec->type);
            }
        }
        $doc->setContent(new Model\DocumentContent($sections));

        //Set Citations
        $citationList = array();
        foreach($postData->citations as $cite) {
            if (isset($cite->content)) {
                $citationList[] = new Model\DocumentCitation($cite->content);
            }
        }
        $doc->setCitations($citationList);

        return $doc;
    }
}

/* EOF: DocumentAPIHandler.php */