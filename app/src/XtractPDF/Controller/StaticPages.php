<?php

namespace XtractPDF\Controller;

use Silex\Application;
use XtractPDF\Library\Controller;
use Silex\ControllerCollection;

/**
 * Static HTML Pages Controller
 */
class StaticPages extends Controller
{
    /**
     * @var Twig_Environment
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
        $routes->get('/about',   array($this, 'aboutAction'))->bind('about');
        $routes->get('/apidocs', array($this, 'apiDocsAction'))->bind('apidocs');
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

    public function aboutAction()
    {
        return $this->render('about.html.twig', "About XtractPDF");
    }

    // --------------------------------------------------------------

    public function apiDocsAction()
    {
        return $this->render('apidocs.html.twig', "XtractPDF API");
    }

    // --------------------------------------------------------------

    /**
     * Render using Twig
     *
     * Detects if Ajax Request, and if so, doesn't render the parent template
     *
     * @param  $toRender  Twig File (e.g about.html.twig)
     * @param  $title     The title of the page being rendered
     * @return string     The rendered page
     */
    private function render($toRender, $title)
    {
        //Setup data
        $data = array('page_title' => $title);

        //Load the pagecontent
        $pageContent = $this->twig->render($toRender, $data);

        //If AJAX, only get the content
        if ($this->isAjax()) {
            return $pageContent;
        }
        else {
            $data['page_content'] = $pageContent;
            return $this->twig->render('static.html.twig', $data);
        }
    }
    
}

/* EOF: StaticPages.php */