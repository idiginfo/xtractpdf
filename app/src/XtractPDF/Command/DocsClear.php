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