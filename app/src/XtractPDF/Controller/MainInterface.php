<?php

namespace XtractPDF\Controller;

use Silex\Application;
use XtractPDF\Library\Controller;
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
     * @var array
     */
    private $extractors;

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
        $this->twig       = $app['twig'];
        $this->extractors = $app['extractors'];
    }

    // --------------------------------------------------------------

    /**
     * Index HTML Page
     *
     * GET /
     */
    public function indexAction()
    {
        //Dynamic data for the main interface
        $data = array(
            'title'            => 'XtractPDF',
            'description'      => 'Extract text from PDFs',
        );

        //Display it
        if ($this->clientExpects('json')) {
            $data['extractors'] = $this->extractors->getExtractorInfo();
            return $this->json($data);            
        }
        else {

            $data['page_class']       = 'maininterface';
            $data['extractors']       = $this->extractors;
            $data['defaultExtractor'] = 'poppler';

            return $this->twig->render('index.html.twig', $data);
        }
    }
}

/* EOF: MainInterface.php */