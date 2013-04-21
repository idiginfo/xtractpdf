<?php

namespace XtractPDF;

use Silex\WebTestCase;

abstract class XtractPDFControllerTestCase extends WebTestCase
{
    /**
     * Implement the createApplication method to be used in all controller tests
     */
    public function createApplication()
    {
        $app = new App(App::DEVELOP);
        $app['exception_handler']->disable();

        return $app;
    }
}

/* EOF: XtractPDFControllerTestCase.php */