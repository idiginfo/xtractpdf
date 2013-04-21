<?php

namespace XtractPDF\Extractor;

use Symfony\Component\Process\ProcessBuilder;
use XtractPDF\Library\ExtractorException;

/**
 * PopplerPDFtoTxt Extractor
 */
class PopplerPDFtoTxt implements ExtractorInterface
{
    /**
     * @var string  The python command to perform the conversion
     */
    private $cmd;
    
    /**
     * @var Symfony\Component\Process\ProcessBuilder
     */ 
    private $proc;

    // --------------------------------------------------------------

    
    /**
     * Constructor
     *
     * @param Symfony\Component\Process\ProcessBuilder
     * @param string  The command (null=default)
     */
    public function __construct(ProcessBuilder $proc = null, $cmd = null)
    {
        $this->cmd  = $cmd ?: realpath('/usr/bin/pdftotext');
        $this->proc = $proc ?: new ProcessBuilder();

        if ( ! is_executable($this->cmd)) {
            throw new ExtractorException("The command does not exist or is not executable: ". $this->cmd);
        }        
    }

    // --------------------------------------------------------------

    static public function getSlug()
    {
        return 'poppler';
    }

    // --------------------------------------------------------------

    static public function getName()
    {
        return "Poppler PDF to Text";
    }

    // --------------------------------------------------------------

    static public function getDescription()
    {
        return "A CLI PDF to Text Tool";        
    }

    // --------------------------------------------------------------

    static public function getLink()
    {
        return "http://poppler.freedesktop.org/";
    }

    // --------------------------------------------------------------

    /**
     * Extract text from PDF file
     *
     * @param string  $file    Realpath to file
     * @return string|boolean  False if could not be converted
     */
    public function extract($filepath)
    {
        //Clone the process builder
        $proc = clone $this->proc;

        //Build the command
        $proc->add($this->cmd);
        $proc->add('-q');
        $proc->add($filepath); 
        $proc->add('-');       

        //Get the process and run it
        $process = $proc->getProcess();
        $process->run();

        if ($process->isSuccessful()) {
            return $process->getOutput();
        }
        else {
            throw new ExtractorException($process->getExitCodeText());
        }

        //Return the output
        return $process->getOutput();       
    }
}

/* EOF: PopplerPDFtoTxt.php */