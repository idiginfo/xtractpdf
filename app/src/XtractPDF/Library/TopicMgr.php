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

use XtractPDF\Model\Topic;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Events;

/**
 * Topic Manager
 *
 */
class TopicMgr
{
    /**
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    // --------------------------------------------------------------

    /**
     * Constructor
     *
     * @param Doctrine\ODM\MongoDB\DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        //Set Dependencies
        $this->dm   = $dm;
        $this->repo = $dm->getRepository('\XtractPDF\Model\Topic');
    }

    // --------------------------------------------------------------

    /**
     * Creates or updates a topic based on its primary term
     */
    public function putTopic(Topic $topic)
    {
        $existing = $this->getTopic($topic->mainTerm);

        if ($existing) {
            $existing->setTerms($topic->terms);
            $topic = $existing;
        }

        $this->dm->persist($topic);
        $this->dm->flush();
    }

    // --------------------------------------------------------------

    public function getTopics()
    {
        return $this->repo->findAll();
    }

    // --------------------------------------------------------------

    public function getTopic($mainTerm)
    {
        return $this->repo->findOneBy(array('mainTerm' => $mainTerm));
    }

    // --------------------------------------------------------------

    public function deleteAll()
    {
        foreach($this->getTopics() as $topic) {
            $this->dm->remove($topic);
        }

        $this->dm->flush();
    }  

    // --------------------------------------------------------------

    public function deleteTopic(Topic $topic)
    {
        $this->dm->remove($topic);
        $this->dm->flush();
    }
}

/* EOF: TopicMgr.php */