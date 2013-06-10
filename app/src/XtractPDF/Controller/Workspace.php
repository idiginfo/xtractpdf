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

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var XtractPDF\Extractor\ExtractorInterface
     */
    private $extractor;

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
        $this->twig      = $app['twig'];        
        $this->docMgr    = $app['doc_mgr'];
        $this->extractor = $app['extractor'];
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

            $doc = $this->docMgr->getDocument($id);

            if ( ! $doc->isExtracted) {

                //Run the PDFX converter to get the XML
                $this->extractor->extract();

                //Process XML into data model
            }
            
            //Process data model into Twig View
            $this->twig->render('worksapce.html.twig', array('doc' => $doc));
        }
        else {
            return $this->abort(404, "Could not find document");
        }
    }    
}

/* EOF: Workspace */