<?php

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
class Extract extends BaseCommand
{
    /**
     * @var XtractPDF\Library\DocumentMgr
     */
    private $docMgr;

    // --------------------------------------------------------------

    protected function configure()
    {
        $this->setName('docs:clear')->setDescription('Clear all documents in the system');
    }

    // --------------------------------------------------------------

    public function init(Application $app)
    {
        $this->docMgr = $app['doc_mgr'];        
    }

    // --------------------------------------------------------------

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("GONNA CLEAR");
    }
}

/* EOF: Extract.php */