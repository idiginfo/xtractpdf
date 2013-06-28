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
use XtractPDF\Helper\FancyFileIterator;
use RuntimeException, InvalidArgumentException;
use Silex\Application;
use Exception;

/**
 * Render a Document
 */
class DocsRender extends BaseCommand
{    
    /**
     * @var XtractPDF\Library\DocumentMgr
     */
    protected $docMgr;

    // --------------------------------------------------------------

    protected function configure()
    {
        $this->setName('docs:render')->setDescription('Render an existing document in the system');        
        $this->addArgument('renderer', InputArgument::REQUIRED, 'Which renderer to use (run \'info renderers\' to see list of renderers');
        $this->addArgument('id', InputArgument::REQUIRED, 'Unique ID of the document');
    }

    // --------------------------------------------------------------

    public function init(Application $app)
    {
        $this->docMgr      = $app['doc_mgr'];
        $this->rendererBag = $app['renderers'];             
    }

    // --------------------------------------------------------------

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Parameters
        $rendName = $input->getArgument('renderer');
        $docId    = $input->getArgument('id');

        //Get the doc from its ID
        if ( ! $doc = $this->docMgr->getDocument($docId)) {
            throw new RuntimeException("No document exists with ID: " . $docId);
        }

        //Try to set the renderer
        try {
            $renderer = $this->rendererBag->get($rendName);
        }
        catch (InvalidArgumentException $e) {
            throw new RuntimeException("Invalid renderer: " . $rendName . ".  Use 'info renderers' to see available renderers");    
        }

        //Output it
        $output->writeln($renderer->serialize($doc));
    }
}
/* EOF: DocsRender.php */