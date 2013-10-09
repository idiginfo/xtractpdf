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
use XtractPDF\Model\Topic;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException, InvalidArgumentException, Exception;
use Silex\Application;

/**
 * Load Topics
 */
class TopicsLoad extends BaseCommand
{
    // --------------------------------------------------------------

    protected function configure()
    {
        $this->setName('topics:load')->setDescription('Load topics from file');     
        $this->addOption('clear', 'c', InputOption::VALUE_NONE, 'Optionally clear existing topics');
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to topics CSV file');
    }

    // --------------------------------------------------------------

    public function init(Application $app)
    {
        $this->topicMgr = $app['topic_mgr'];
    }

    // --------------------------------------------------------------

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file  = $input->getArgument('path');
        $clear = $input->getOption('clear');

        if ( ! is_readable($file)) {
            throw new RuntimeException("Cannot read from file: " . $file);
        }

        if ($clear) {
            $output->write("Clearing all existing topics in the system. . .");
            $this->topicMgr->deleteAll();
            $output->writeln("Done");
        }

        $count = 0;

        $handle = fopen($file, 'r');
        while ($terms = fgetcsv($handle)) {

            //Create a new topic object
            $topic = new Topic(reset($terms), $terms);

            //Save it
            $this->topicMgr->putTopic($topic);

            //Output
            $output->writeln(sprintf(
                "Loaded topic: <comment>%s</comment> (%s)",
                $topic->mainTerm,
                (count($topic->terms) > 3) ? implode(', ', array_slice($topic->terms, 0, 3)) . '...': implode(', ', $topic->terms)
            ));

            $count++;
        }

        $output->writeln(sprintf(
            "\nLoaded <info>%s</info> topics (total <info>%s</info> topics)", 
            number_format($count, 0),
            number_format(count($this->topicMgr->getTopics()), 0)
        ));
    }
}

/* EOF: TopicsLoad.php */