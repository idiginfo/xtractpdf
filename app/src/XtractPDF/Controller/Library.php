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

class Library extends Controller
{

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
        $routes->get('/library',        array($this, 'indexAction'))->bind('library');
        $routes->get('/library/{id}',   array($this, 'singleAction'))->bind('library_single');
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
        $this->twig   = $app['twig'];        
        $this->docMgr = $app['doc_mgr'];
    }

    // --------------------------------------------------------------

    public function indexAction()
    {
        //If search query, get results and include them in the data

        //Render the response
        if ($this->clientExpects('json')) {
            return $this->json(array('todo' => 'this'));
        }
        else {
            return $this->twig->render('pages/library/index.html.twig');
        }

        
    }

    // --------------------------------------------------------------

    public function singleAction()
    {
        //Render the response
        if ($this->clientExpects('json')) {

        }
        else {
            return $this->twig->render('pages/library/single.html.twig');
        }
    }
}

/* EOF: Library.php */