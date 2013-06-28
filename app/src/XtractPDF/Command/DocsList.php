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
 * List Documents
 */
class DocsList extends BaseCommand
{    
    /**
     * @var XtractPDF\Library\DocumentMgr
     */
    protected $docMgr;

    // --------------------------------------------------------------

    protected function configure()
    {
        $this->setName('docs:list')->setDescription('List Documents in System');        
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Optional limit');
        $this->addOption('skip',  's', InputOption::VALUE_REQUIRED, 'Optional skip offset');
        $this->addOption('query', '',  InputOption::VALUE_REQUIRED, 'Optional search query');
    }

    // --------------------------------------------------------------

    public function init(Application $app)
    {
        $this->docMgr = $app['doc_mgr'];        
    }

    // --------------------------------------------------------------

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Get the iterator
        $iterator = $this->getList($input, $output);

        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(array('ID', 'Title', 'Created', 'Modified', 'Completed?'));

        foreach($iterator as $item) {
            $table->addRow(array(
                $item->uniqId,
                (isset($item->title) && $item->title) ?: 'Untitled Document',
                date_format($item->created, 'Y-M-d H:m'),
                date_format($item->modified, 'Y-M-d H:m'),
                $item->isComplete ? 'Yes' : 'No'
            ));
        }

        $table->render($output);
    }

    // --------------------------------------------------------------

    protected function getList(InputInterface $input, OutputInterface $output)
    {
        $query = $input->getOption('query');
        $limit = (int) $input->getOption('limit') ?: null;
        $skip  = (int) $input->getOption('skip');

        return $this->docMgr->listDocuments($limit, $query, $skip);
    }
}

/* EOF: DocsList.php */