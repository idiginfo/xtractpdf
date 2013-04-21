<?php

namespace XtractPDF\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RecursiveIteratorIterator, RecursiveDirectoryIterator, SplFileInfo;
use Silex\Application;

class DeleteOldFiles extends SymfonyCommand
{
    const FAILED  = 0;
    const DELETED = 1;
    const SKIPPED = 2;

    // --------------------------------------------------------------

    /**
     * @var string
     */
    private $uploadPath;

    /**
     * @var int
     */
    private $ttl;

    // --------------------------------------------------------------

    /**
     * Constructor
     *
     * @param string $uploadPath
     * @param int    $ttl         File Time To Live
     */
    public function __construct($uploadPath, $ttl = 300)
    {
        parent::__construct();

        $this->uploadPath = $uploadPath;
        $this->ttl        = $ttl;
    }

    // --------------------------------------------------------------

    protected function configure()
    {
        $this->setName('delfiles')->setDescription('Command to delete old files');
    }

    // --------------------------------------------------------------

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ( ! is_readable($this->uploadPath)) {
            throw new RuntimeException("The folder is unreadable: " . $this->uploadPath);
        }

        //Message
        $output->writeln(sprintf(
            "Checking for files in %s that are older than %s seconds old",
            $this->uploadPath,
            $this->ttl
        ));

        //Iterator
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->uploadPath));

        //Reporting
        $numDeleted = 0;
        $numSkipped = 0;
        $numFailed  = 0;

        //Do it
        foreach ($iterator as $file) {

            if (strcasecmp($file->getExtension(), 'pdf') == 0) {
                
                switch ($this->conditionallyDelete($file)) {
                    case self::FAILED: 
                        $numFailed++;
                        $output->writeln("Failed deleting " . $file->getBaseName());
                    break;
                    case self::DELETED:
                        $numDeleted++;
                        $output->writeln("Deleted " . $file->getBaseName());
                    break;
                    case self::SKIPPED;
                        $numSkipped++;
                    break;
                }
            }
        }

        //Report
        $output->writeln(sprintf(
            "Deleted %s | Skipped %s | Errors %s", 
            number_format($numDeleted, 0),
            number_format($numSkipped, 0),
            number_format($numFailed, 0)
        ));
    }

    // --------------------------------------------------------------

    /**
     * Delete a file if it is older than the ttl
     *
     * @param  SplFileInfo $file
     * @return int
     */
    protected function conditionallyDelete(SplFileInfo $file)
    {
        if (time() - $file->getMTime() > $this->ttl) {
            return (int) unlink((string) $file);
        }
        else {
            return self::SKIPPED;
        }
    }
}


/* EOF DeleteOldFiles.php */