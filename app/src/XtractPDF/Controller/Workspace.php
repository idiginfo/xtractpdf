<?php

namespace XtractPDF\Controller;

use Silex\Application;
use XtractPDF\Library\Controller;
use Silex\ControllerCollection;

/**
 * Workspace Controller
 */
class Workspace extends Controller
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
        $routes->get('/workspace/{file}', array($this, 'renderWorkspaceAction'))->bind('workspace');
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
     * Render a PDF and then destroy it
     *
     * GET /pdf
     * 
     * @param string $file  The filename
     */
    public function renderWorkspaceAction($file)
    {
        //Get the filepath
        $filepath = $this->filepath . '/' . $file;

        //If the file is readable, then send it; else 404
        if (is_readable($filepath)) {
            return "<p>Would render workspace for " . basename($filepath) . "</p>";
        }
        else {
            return $this->abort(404, "Could not find PDF file");
        }
    }    
}

/* EOF: Workspace */