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
 * List Documents
 */
class Info extends BaseCommand
{    
    /**
     * @var XtractPDF\Library\DocumentMgr
     */
    protected $docMgr;

    /**
     * @var Configula\Config
     */
    protected $config;

    // --------------------------------------------------------------

    protected function configure()
    {
        $this->setName('info')->setDescription('Get general information about the system');
        $this->addArgument('which', InputArgument::OPTIONAL, 'Which data to show (comma-separated)');
    }

    // --------------------------------------------------------------

    public function init(Application $app)
    {
        $this->docMgr      = $app['doc_mgr'];
        $this->config      = $app['config'];
        $this->builderBag  = $app['builders'];
        $this->rendererBag = $app['renderers'];
    }

    // --------------------------------------------------------------

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $which = array_filter(array_map('trim', explode(', ', $input->getArgument('which'))));

        if (in_array('stats', $which) OR empty($which)) {
            $this->showStats($output);
        }
        if (in_array('config', $which) OR empty($which)) {
            $this->showConfig($output);   
        }        
        if (in_array('builders', $which) OR empty($which)) {
            $this->showBuilders($output);              
        }
        if (in_array('renderers', $which) OR empty($which)) {
            $this->showRenderers($output);              
        }        
    }

    // -----------------------------------------------------------------

    private function showStats(OutputInterface $output)
    {
        //Stats
        $statsTable = new TableHelper();
        $statsTable->setHeaders(array('Item', 'Number'));

        $statsTable->addRow(array('Number of Documents', count($this->docMgr->listDocuments())));

        $output->writeln("\nStatistics");
        $statsTable->render($output);
    }

    // -----------------------------------------------------------------

    private function showConfig(OutputInterface $output)
    {
        //Configuration settings
        $configTable = new TableHelper();
        $configTable->setHeaders(array('Item', 'Value'));

        foreach($this->config->getItems() as $item => $val) {

            if ( ! is_scalar($val)) {

                if ($val = json_encode($val)) {
                    $val = stripcslashes($val);
                } else {
                    $val = '[OBJECT]';
                }
            }

            $configTable->addRow(array($item, $val));
        }

        $output->writeln("\nConfiguration");
        $configTable->render($output);
    }

    // -----------------------------------------------------------------

    private function showBuilders(OutputInterface $output)
    {
        //List of builders
        $builderTable = new TableHelper();
        $builderTable->setHeaders(array('Handle', 'Name', 'Description'));

        foreach($this->builderBag->getAll() as $handle => $obj) {
            $builderTable->addRow(array($handle, $obj->getName(), $obj->getDescription() ));
        }

        $output->writeln("\nBuilders");
        $builderTable->render($output);
    }

    // -----------------------------------------------------------------

    private function showRenderers(OutputInterface $output)
    {
        //List of builders
        $rendererTable = new TableHelper();
        $rendererTable->setHeaders(array('Handle', 'Name', 'Description'));

        foreach($this->rendererBag->getAll() as $handle => $obj) {
            $rendererTable->addRow(array($handle, $obj->getName(), $obj->getDescription() ));
        }

        $output->writeln("\nRenderers");
        $rendererTable->render($output);
    }

}
/* EOF: Info.php */