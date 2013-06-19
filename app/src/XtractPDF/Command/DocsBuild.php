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
 * List Documents
 */
class DocsBuild extends BaseCommand
{    
    /**
     * @var XtractPDF\Library\DocumentMgr
     */
    protected $docMgr;

    // --------------------------------------------------------------

    protected function configure()
    {
        $this->setName('docs:build')->setDescription('List Documents in System');        
        $this->addOption('persist',   'p', InputOption::VALUE_NONE,     'Persist the document(s)');
        $this->addOption('render' ,   'r', InputOption::VALUE_REQUIRED, 'Render each document with the desired renderer after building rather than displaying a simple message');
        $this->addOption('recursive', '',  InputOption::VALUE_NONE,     'Recursively parse directory for files to build');

        $this->addArgument('builder', InputArgument::REQUIRED, 'Which builder to use when building (run \'info builders\' to see list of builders');
        $this->addArgument('path',    InputArgument::REQUIRED, 'Path to file or directory.  If directory, use --recrusive to parse recursively');
    }

    // --------------------------------------------------------------

    public function init(Application $app)
    {
        $this->docMgr      = $app['doc_mgr'];
        $this->builderBag  = $app['builders'];
        $this->rendererBag = $app['renderers'];             
    }

    // --------------------------------------------------------------

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Parameters
        $persist  = $input->getOption('persist');
        $rendName = $input->getOption('render');
        $recurse  = $input->getOption('recursive');
        $bldrName = $input->getArgument('builder');
        $path     = $input->getArgument('path');

        //Validate path
        if ( ! is_readable($path)) {
            throw new RuntimeException("Invalid path: " . $path);
        }

        //Try to set the renderer
        if ($rendName) {
            try {
                $renderer = $this->rendererBag->get($rendName);
            }
            catch (InvalidArgumentException $e) {
                throw new RuntimeException("Invalid renderer: " . $rendName . ".  Use 'info renderers' to see available renderers");    
            }
        }
        else {
            $renderer = null;
        }

        //Try to set the builder
        try {
            $builder = $this->builderBag->get($bldrName);
        }
        catch (InvalidArgumentException $e) {            
            throw new RuntimeException("Invalid builder: " . $bldrName . ".  Use 'info builders' to see available builders");
        }
    
        //Get an iterator for the path
        $iterator = new FancyFileIterator($path, $recurse, '.pdf', 'application/pdf');

        //Count of files processed
        $count = 0;
        $skip  = 0;

        //Do it!
        foreach($iterator as $file) {
            $result = $this->buildDocument($output, $file->getRealPath(), $persist, $builder, $renderer);
            ($result) ? $count++ : $skip++;
        }

        //Formulate "done" message
        if ($count > 0 OR $skip > 0) {
            $msg = sprintf(
                "<fg=%s>Done.  Processed %s files.</fg=%s>",
                ($count > 0) ? 'green' : 'yellow',
                number_format($count),
                ($count > 0) ? 'green' : 'yellow'
            );

            if ($skip > 0) {
                $msg .= sprintf(" <fg=yellow>Skipped %s files.</fg=yellow>", number_format($skip));
            }

        }
        else {
            $msg = "No documents found to persist at path: " . realpath($path);
        }
            
        //Write out done message
        $output->writeln($msg);
    }

    // --------------------------------------------------------------

    private function buildDocument($output, $filePath, $persist, $builder, $renderer)
    {
        //Get the UniqID based on the file MD5
        $uniqId = md5_file($filePath);

        //Generate a display-friendly version of the filename
        $dispName = (strlen(basename($filePath)) > 25)
            ? substr(basename($filePath), 0, 25) . "..."
            : basename($filePath);


        //If persist mode on, check to ensure we haven't already built
        //it before we take the time to do that
        if ($persist && $this->docMgr->getDocument($uniqId)) {
            $output->writeln("<fg=yellow>Skipping " . $dispName . " (already exists)</fg=yellow>");
            return false;
        }

        //Build Message
        $output->write(sprintf(
            "Building <fg=yellow>%s</fg=yellow> with <fg=yellow>%s</fg=yellow> (may take a moment)...",
            $dispName,
            $builder->getName()
        ));

        //Build result, or die trying
        try {
            $doc = $builder->build($filePath, new DocumentModel($uniqId));
            $output->write("<fg=green>Done</fg=green>");
        }
        catch (Exception $e) {
            $output->write(sprintf("<fg=red>Error: %s</fg=red>", $e->getMessage()));
            return false;
        }

        //Persist message
        if (isset($doc) && $persist) {
            $output->write(" Persisting...");

            try {
                $this->docMgr->saveNewDocument($doc, $filePath);
                $output->write("<fg=green>Done</fg=green>");
            }
            catch (Exception $e) {
                $output->write(sprintf("<fg=red>Error: %s</fg=red>", $e->getMessage()));
            }
        }
        
        //Newline
        $output->writeln('');

        //Render it if we've elected to do so
        if ($renderer) {
            $output->writeln($renderer->serialize($doc));
        }

        return true;
    }
}
/* EOF: DocsBuild.php */