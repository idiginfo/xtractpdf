<?php

namespace XtractPDF\Library;

use PHPUnit_Framework_TestCase, Mockery;

class PDFConverterTest extends PHPUnit_Framework_TestCase
{    
    public function testInstantiateSucceeds()
    {
        $obj = new PDFConverter();
        $this->assertInstanceOf('XtractPDF\Library\PDFConverter', $obj);
    }

    // --------------------------------------------------------------

    public function testConvertWorksWithDefaults()
    {
        $sampleFile = $this->getTestFile();
        $obj = new PDFConverter();

        $this->assertContains('Author', $obj->convert($sampleFile));
    }

    // --------------------------------------------------------------

    public function testConvertThrowsRuntimeExceptionForInvalidPdfCmd()
    {
        $this->setExpectedException("\RuntimeException");

        $sampleFile = $this->getTestFile();

        $obj = new PDFConverter(null, '/does/not/exist.py');
        $obj->convert($sampleFile);
    }

    // --------------------------------------------------------------

    protected function getTestFile()
    {
        return realpath(__DIR__ . '/../Fixtures/sample.pdf');
    }
}

/* EOF: PDFConverterTest.php */