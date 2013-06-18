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
class DocsClear extends BaseCommand
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
        $cursor   = $this->docMgr->listDocuments();
        $docCount = count($cursor);

        if ($docCount == 0) {
            $output->writeln("There are no documents in the system");
            return;
        }

        if ( ! $input->getOption('no-interaction')) {

            $dialog = $this->getHelperSet()->get('dialog');
            $msg    = sprintf(
                "<error>This will clear all %s documents in the system!\n\nThere is no going back.</error>\n\n<question>Are you sure [type 'yes']??</question> ",
                number_format($docCount, 0)
            );

            if ( ! $dialog->askConfirmation($output, $msg, false)) {
                $output->writeln("Cancelled Action.");
                return;
            }
        }

        foreach ($cursor as $doc) {
            $this->docMgr->removeDocument($doc, false);
        }
        $this->docMgr->flush();

        $output->writeln(sprintf("Cleared %s documents", $docCount));
    }
}

/* EOF: Extract.php */