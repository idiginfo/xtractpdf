<?php

namespace XtractPDF\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use XtractPDF\Core\Controller;
use XtractPDF\Model;

/**
 * Library Controller
 */
class Library extends Controller
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
     * @var XtractPDF\Library\BuilderBag
     */
    private $builders;

    /**
     * @var array
     */
    private $viewData = array();

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
        $routes->get('/',                 array($this, 'indexAction'));
        $routes->get('/library',          array($this, 'libraryAction'));
        $routes->get('/library/{uniqId}', array($this, 'singleAction'));
        
        $routes->post('/library',         array($this, 'uploadAction'));
        $routes->post('/library/{id}',    array($this, 'updateAction'));
    }

    // --------------------------------------------------------------

    /**
     * The init method is run upon the controller executing
     *
     * Pull libraries form the DiC here in child classes
     */
    protected function init(Application $app)
    {        
        //Load dependencies
        $this->twig      = $app['twig'];        
        $this->docMgr    = $app['doc_mgr'];
        $this->builders  = $app['builders'];

        //Set the page class for twig views
        $this->viewData['page_class'] = 'workspace';
    }

    // --------------------------------------------------------------

    /**
     * GET /
     */
    public function indexAction()
    {
        return $this->twig->render('pages/library.html.twig', $this->viewData);
    }

    // --------------------------------------------------------------

    /**
     * GET /library
     */
    public function libraryAction()
    {
        if ($this->clientExpects('json')) {

            //Determine limit, offset, search params

            //Get list of items from the docMgr

            //Return it as JSON

        }
        else { //Do HTML
            return $this->twig->render('pages/library.html.twig', $this->viewData);
        }
    }

    // --------------------------------------------------------------
    
    /**
     * GET /library/{uniqId}
     *
     * @param $uniqId  The identifier for a document object
     */
    public function singleAction($uniqId)
    {
        if ($this->clientExpects('json')) {

            //Get the item from the database (or 404 if it doesn't exist)

            //Render it as a JSON object

            //Return it as JSON
        }
        else { //Do HTML
            return $this->twig->render('pages/library-single.html.twig', $this->viewData);
        }
    }    

    // --------------------------------------------------------------
    
    /**
     * POST /library
     */
    public function uploadAction()
    {
        //Upload the document

        //Persist it with the persister

        //Get the document id
        $uniqId = 'whatever_the_uploaded_and_saved_doc_was';

        //Return a response
        if ($this->clientExpects('json')) {
            //Return JSON notification that everything went well and URL pointer to the document
        }
        else { //Do HTML redirect
            return $this->redirect('/single/' . $uniqId);
        }        
    }

    // --------------------------------------------------------------

    /**
     * POST /library/{uniqId}
     */
    public function updateAction($uniqId)
    {
        //Check if document exists, otherwise render a 404

        //Get the model of the document from the docMgr      

        //Update the model using the fromJson builder (to be written)

        //Return a response
        if ($this->clientExpects('json')) {
            //Return JSON notification that everything went well and URL pointer to the document
        }
        else { //Do HTML redirect
            return $this->redirect('/single/' . $uniqId);
        }           
    }
}    

/* EOF: Library.php */