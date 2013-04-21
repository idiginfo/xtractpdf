<?php

namespace XtractPDF\Controller;

use XtractPDF\XtractPDFControllerTestCase;

class ConverterTest extends XtractPDFControllerTestCase
{
    public function testFrontPageLoads()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');        
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Scholar2Txt")'));
    }

    public function testUploadPageWorksWithValidPdf()
    {
        $sampleFile = __DIR__ . '/../../../../../samples/sample4.pdf';
        
        if ( ! file_exists($sampleFile)) {
            $this->fail('Could not load the sample file: ' . $sampleFile);
        }

        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        $form = $crawler->selectButton('Upload')->form();
        $form['pdffile'] = $sampleFile;
        $crawler = $client->submit($form);

    }
}

/* EOF: ConverterTest.php */