<?php

namespace XtractPDF\Controller;

use Silex\Application;
use XtractPDF\Core\Controller;
use Silex\ControllerCollection;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Workspace Controller
 */
class Workspace extends Controller
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
        $routes->get('/workspace/{id}', array($this, 'renderWorkspaceAction'))->bind('workspace');
    }

    // --------------------------------------------------------------

    /**
     * The init method is run upon the controller executing
     *
     * Pull libraries form the DiC here in child classes
     */
    protected function init(Application $app)
    {        
        $this->docMgr   = $app['doc_mgr'];
    }

    // --------------------------------------------------------------

    /**
     * Render a PDF and then destroy it
     *
     * GET /workspace/{id}
     * 
     * @param string $id  Unique Identifier of the document
     */
    public function renderWorkspaceAction($id)
    {

        //If the file is readable, then send it; else 404
        if ($this->docMgr->checkDocumentExists($id)) {

            //Run the PDFX converter to get the XML
            //Process XML into data model
            //Process data model into Twig View

            return "<p>Would render workspace for " . $id . "</p>";
        }
        else {
            return $this->abort(404, "Could not find document");
        }
    }    
}

/* EOF: Workspace */