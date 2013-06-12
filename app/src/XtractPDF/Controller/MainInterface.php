<?php

namespace XtractPDF\Controller;

use Silex\Application;
use XtractPDF\Core\Controller;
use Silex\ControllerCollection;

/**
 * Main Interface Controller
 */
class MainInterface extends Controller
{
    /**
     * @var Twig_Environment $twig
     */
    private $twig;

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
        $routes->get('/', array($this, 'indexAction'))->bind('front');    
    }

    // --------------------------------------------------------------

    /**
     * The init method is run upon the controller executing
     *
     * Pull libraries form the DiC here in child classes
     */
    protected function init(Application $app)
    {        
        $this->twig   = $app['twig'];
        $this->docMgr = $app['doc_mgr'];        
    }

    // --------------------------------------------------------------

    /**
     * Index HTML Page
     *
     * GET /
     */
    public function indexAction($id = null)
    {
        //Get a list of documents in the system
        $data = array(
            'doc_list' => $this->docMgr->listDocuments()
        );

        $this->debug("test");

        //Show the view
        return $this->twig->render('index.html.twig', $data);
    }
}

/* EOF: MainInterface.php */