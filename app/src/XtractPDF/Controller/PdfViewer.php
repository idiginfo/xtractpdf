<?php

namespace XtractPDF\Controller;

use Silex\Application;
use XtractPDF\Library\Controller;
use Silex\ControllerCollection;

/**
 * PDF Viewer Controller
 */
class PdfViewer extends Controller
{
    /**
     * @var string Filepath of uploads
     */
    private $filepath;

    // --------------------------------------------------------------

    /**
     * Set the routes
     *
     * Be sure to only set routes in here, and load all other resources
     * in self::init() for performance reasons
     *
     * Run $app->get(), $app->match(), etc.. in this method
     *
     * @param Silex\Application $app
     */
    protected function setRoutes(ControllerCollection $routes)
    {
        $routes->get('/pdf/{file}', array($this, 'renderPdfAction'))->bind('viewpdf');
    }

    // --------------------------------------------------------------

    /**
     * The init method is run upon the controller executing
     *
     * Pull libraries form the DiC here in child classes
     */
    protected function init(Application $app)
    {        
        $this->filepath = $app['pdf_filepath'];
    }

    // --------------------------------------------------------------

    /**
     * Render a PDF by its unique identifier
     *
     * GET /pdf/{pdfIdentifier}
     * 
     * @param string $file  The filename
     */
    public function renderPdfAction($file)
    {
        //Get the filepath
        $filepath = $this->filepath . '/' . $file;

        //If the file is readable, then send it; else 404
        if (is_readable($filepath)) {
            return $this->sendFile($filepath);
        }
        else {
            return $this->abort(404, "Could not find PDF file");
        }
    }    
}

/* EOF: PdfViewer.php */