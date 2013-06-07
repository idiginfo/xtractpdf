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
        $this->twig = $app['twig'];
    }

    // --------------------------------------------------------------

    /**
     * Index HTML Page
     *
     * GET /
     */
    public function indexAction()
    {
        return $this->twig->render('index.html.twig');
    }
}

/* EOF: MainInterface.php */