<?php

namespace XtractPDF\Extractor;

use Symfony\Component\Process\ProcessBuilder;
use XtractPDF\Library\ExtractorException;

/**
 * PDFMiner Extractor
 */
class PDFMiner implements ExtractorInterface
{
    /**
     * @var string  The python command to perform the conversion
     */
    private $pdfCmd;
    
    /**
     * @var Symfony\Component\Process\ProcessBuilder
     */ 
    private $proc;

    // --------------------------------------------------------------

    
    /**
     * Constructor
     *
     * @param Symfony\Component\Process\ProcessBuilder
     * @param string  The python command to run for conversion(null = default)
     */
    public function __construct(ProcessBuilder $proc = null, $pdfCmd = null)
    {
        $this->pdfCmd = $pdfCmd ?: realpath('/usr/bin/pdf2txt');
        $this->proc  = $proc ?: new ProcessBuilder();

        if ( ! is_executable($this->pdfCmd)) {
            throw new ExtractorException("The command does not exist or is not executable: ". $this->pdfCmd);
        }
    }

    // --------------------------------------------------------------

    static public function getSlug()
    {
        return 'pdfminer';
    }

    // --------------------------------------------------------------

    static public function getName()
    {
        return "PDFMiner";
    }

    // --------------------------------------------------------------

    static public function getDescription()
    {
        return "A Python library for extracting text from a PDF";        
    }

    // --------------------------------------------------------------

    static public function getLink()
    {
        return "http://www.unixuser.org/~euske/python/pdfminer/";
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
        $proc->add($this->pdfCmd);
        $proc->add('-t');
        $proc->add('text');
        $proc->add('-A');
        $proc->add($filepath);        

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
        $output = $process->getOutput();
        return ($output == 'False') ? false : $output;  
    }
}

/* EOF: PDFMiner.php */
