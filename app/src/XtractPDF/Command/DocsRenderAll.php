<?php

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
class DocsRenderAll extends BaseCommand
{    
    /**
     * @var XtractPDF\Library\DocumentMgr
     */
    protected $docMgr;

    // --------------------------------------------------------------

    protected function configure()
    {
        $this->setName('docs:renderall')->setDescription('Render all docs in the system');     

        $this->addOption('completed', 'c', InputOption::VALUE_NONE,     "Render only documents marked as 'complete'");
        $this->addOption('outputdir', 'o', InputOption::VALUE_REQUIRED, 'Optionally output each item to a separate file in the specified directory');
        
        $this->addArgument('renderer', InputArgument::REQUIRED, 'Which renderer to use (run \'info renderers\' to see list of renderers');
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
        $rendName     = $input->getArgument('renderer');
        $outputDir    = $input->getOption('outputdir');
        $completeOnly = (boolean) $input->getOption('completed');

        //Try to set the renderer
        try {
            $renderer = $this->rendererBag->get($rendName);
        }
        catch (InvalidArgumentException $e) {
            throw new RuntimeException("Invalid renderer: " . $rendName . ".  Use 'info renderers' to see available renderers");    
        }

        //Ouputting to output dir?  Make sure it is writable
        if ( ! is_writable(realpath($outputDir))) {
            throw new RuntimeException("The output directory is not writable (check permissions): " . realpath($outputDir));
        }

        $count = 0;

        //Foreach document...
        foreach($this->docMgr->listDocuments() as $doc) {

            //If it is not complete, and we only want complete, continue
            if ($completeOnly && ! $doc->isComplete) {
                continue;
            }

            if ($outputDir) {
                $fname = realpath($outputDir) . DIRECTORY_SEPARATOR . $doc->uniqId . '.' . $renderer->getExt();
                file_put_contents($fname, $renderer->serialize($doc));
                $count++;
            }
            else {
                //Output it
                $output->writeln($renderer->serialize($doc));
            }
        }

        //Output a report if we're writing to outputDir
        if ($outputDir) {
            $output->writeln(sprintf("Wrote %s files", number_format($count, 0)));
        }
    }
}
/* EOF: DocsRender.php */