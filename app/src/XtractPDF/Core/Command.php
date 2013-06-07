<?php

namespace XtractPDF\Core; 

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Silex\Application;

/**
 * Abstract Base Command
 */
abstract class Command extends SymfonyCommand
{
    /**
     * Initialize the Command
     *
     * @param Silex\Application
     */
    public function init(Application $app)
    {
        //pass - meant to be overriden
    }
}

/* EOF: Command.php */