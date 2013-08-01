<?php
  
/**
 *   XtractPDF - A PDF Content Extraction and Curation Tool
 *
 *   This program is free software under the GNU General Public License (v2)
 *   See LICENSE.md for a complete copy of the license
 *
 * @package     XtractPDF
 * @author      Florida State University iDigInfo (http://idiginfo.org)
 * @copyright   Copyright (C) 2013 Florida State University (http://fsu.edu)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt
 * @link        http://idiginfo.org
 */

// ------------------------------------------------------------------

namespace XtractPDF\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use XtractPDF\Core\Controller;
use XtractPDF\Model;
use Twig_Error;
//Temporary
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

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
        $routes->get('/about',            array($this, 'aboutAction'))->bind('about');
        $routes->get('/about/{page}',     array($this, 'aboutAction'))->bind('about_subpage');
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
            ? 'about-' . $subPage
            : 'about';
        
        return $this->twig->render('pages/' . $pageName . '.html.twig');
        // try {
        //     return $this->twig->render('pages/' . $pageName . '.html.twig');
        // }
        // catch (Twig_Error $e) {
        //     return $this->abort(404, "Page Not Found");
        // }
    }
}

/* EOF: About.php */