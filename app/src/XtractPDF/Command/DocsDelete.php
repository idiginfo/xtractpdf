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
class DocsDelete extends BaseCommand
{
    /**
     * @var XtractPDF\Library\DocumentMgr
     */
    protected $docMgr;

    // --------------------------------------------------------------

    protected function configure()
    {
        $this->setName('docs:delete')->setDescription('Clear all documents in the system');
        $this->addArgument('ids', InputArgument::REQUIRED, 'List of comma-separated document IDs to delete');
    }

    // --------------------------------------------------------------

    public function init(Application $app)
    {
        $this->docMgr = $app['doc_mgr'];        
    }

    // --------------------------------------------------------------

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $iterator = $this->getDocs($input, $output);
        $docCount = count($iterator);

        if ($docCount == 0) {
            $output->writeln($this->noResultMessage());
            return;
        }

        if ( ! $input->getOption('no-interaction')) {

            $dialog = $this->getHelperSet()->get('dialog');
            $msg    = sprintf(
                "<error>This will delete %s documents in the system! There is no going back.</error>\n\n<question>Are you sure [type 'yes']??</question> ",
                number_format($docCount, 0)
            );

            if ( ! $dialog->askConfirmation($output, $msg, false)) {
                $output->writeln("Cancelled Delete.");
                return;
            }
        }

        foreach ($iterator as $doc) {
            $this->docMgr->removeDocument($doc, false);
        }
        $this->docMgr->flush();

        $output->writeln(sprintf("Deleted %s documents", $docCount));
    }

    // --------------------------------------------------------------

    protected function noResultMessage()
    {
        return "No documents were found to delete.  Check IDs and try again";
    }

    // --------------------------------------------------------------

    /**
     * Get an iterator of all of the document objects to delete
     *
     * @return Iterator|array
     */
    protected function getDocs(InputInterface $input, OutputInterface $output)
    {
        $idList = array_filter(
            array_map('trim', explode(',', $input->getArgument('ids')))
        );

        $docs = array();
        foreach($idList as $id) {
            if ($doc = $this->docMgr->getDocument($id)) {
                $docs[] = $doc;
            }
            else {
                $output->writeln("Could not find document with ID $id.  Skipping");
            }
        }
        return $docs;
    }

}

/* EOF: Extract.php */