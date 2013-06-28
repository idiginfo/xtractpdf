<?php
  
/**
 *   XtractPDF - A PDF Content Extraction and Curation Tool
 *
 *   This program is free software under the GNU General Public License (v2)
 *   See LICENSE.md for a complete copy of the license
 *
 * @package     XtractPDF
 * @author      Florida State University iDigInfo (http://idiginfo.org)
 * @copyright   Copyright (C) 2013 Florida State University (http://fsu.edu)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt
 * @link        http://idiginfo.org
 */

// ------------------------------------------------------------------

namespace XtractPDF\Command;

use XtractPDF\Core\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\TableHelper;
use XtractPDF\Model\Document as DocumentModel;
use Silex\Application;
use RuntimeException;

/**
 * Extract XML from PDF via CLI
 */
class DocsClear extends DocsDelete
{
    protected function configure()
    {
        $this->setName('docs:clear')->setDescription('Clear all documents in the system');
    }

    // --------------------------------------------------------------

    protected function noResultMessage()
    {
        return "There are no documents in the system";
    }

    // --------------------------------------------------------------

    /**
     * Get an iterator of all of the document objects to delete
     *
     * @return Iterator|array
     */
    protected function getDocs(InputInterface $input, OutputInterface $output)
    {
        return $this->docMgr->listDocuments();
    }    
}

/* EOF: Extract.php */