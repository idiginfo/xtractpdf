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



        foreach($iterator as $file) {
            $this->buildDocument($output, $file->getRealPath(), $persist, $builder, $renderer);
            $count++;
        }

        $msg = ($count > 0)
            ? sprintf("<fg=green>Done.  Processed %s files</fg=green>", number_format($count))
            : "No documents found to persist at path: " . realpath($path);

        $output->writeln($msg);
    }

    // --------------------------------------------------------------

    private function buildDocument($output, $filePath, $persist, $builder, $renderer)
    {
        //Build the new document
        $output->write(sprintf(
            "Building <fg=yellow>%s</fg=yellow> with <fg=yellow>%s</fg=yellow> (may take a moment)...",
            basename($filePath),
            $builder->getName()
        ));
        $doc = $builder->build($filePath, new DocumentModel(md5_file($filePath)));

        //Persist it?
        if ($persist) {
            $output->write(" Persisting...");
            $result = $this->docMgr->saveNewDocument($doc, $filePath);
            $output->writeln($result ? "<fg=green>Done</fg=green>" : "<fg=yellow>Skipped (already exists)</fg=yellow>");
        }
        else {
            $output->writeln('');
        }

        //Render it
        if ($renderer) {
            $output->writeln($renderer->serialize($doc));
        }
    }
}
/* EOF: DocsBuild.php */