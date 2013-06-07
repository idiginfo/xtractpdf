<?php

namespace XtractPDF\Controller;

use Silex\Application;
use XtractPDF\Core\Controller;
use Silex\ControllerCollection;

/**
 * PDF Viewer Controller
 */
class PdfViewer extends Controller
{
    /**
     * @var XtractPDF\Library\DocumentMgr
     */
    private $docMgr;

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
        $routes->get('/pdf/{id}', array($this, 'renderPdfAction'))->bind('viewpdf');
    }

    // --------------------------------------------------------------

    /**
     * The init method is run upon the controller executing
     *
     * Pull libraries form the DiC here in child classes
     */
    protected function init(Application $app)
    {        
        $this->docMgr = $app['doc_mgr'];
    }

    // --------------------------------------------------------------

    /**
     * Render a PDF by its unique identifier
     *
     * GET /pdf/{id}
     * 
     * @param string $id  Identifier for the document
     */
    public function renderPdfAction($id)
    {
        //If the file is readable, then send it; else 404
        if ($this->docMgr->checkDocumentExists($id)) {
            return $this->stream($this->docMgr->streamPdf($id), 200, array('Content-type' => 'application/pdf'));
        }
        else {
            return $this->abort(404, "Could not find document");
        }
    }    
}

/* EOF: PdfViewer.php */