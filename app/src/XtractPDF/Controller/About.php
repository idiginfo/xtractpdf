<?php


namespace XtractPDF\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use XtractPDF\Core\Controller;
use XtractPDF\Model;
use Twig_Error;

/**
 * 'About' Pages Controller
 */
class About extends Controller
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
        $routes->get('/about',            array($this, 'aboutAction'));
        $routes->get('/about/{page}',     array($this, 'aboutAction'));
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
    }
    
    // --------------------------------------------------------------

    /**
     * Show the desired page
     */
    public function aboutAction($subPage = '')
    {
        $pageName = ($subPage)
            ? 'about'
            : 'about-' . $subPage;

        try {
            return $this->twig->render('pages/' . $pageName . '.html.twig');
        }
        catch (Twig_Error $e) {
            return $this->abort(404, "Page Not Found");
        }
    }
}

/* EOF: About.php */