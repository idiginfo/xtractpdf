<?php
  
/**
 * XtractPDF - A PDF Content Extraction and Curation Tool
 *
 * This program is free software under the GNU General Public License (v2)
 * See LICENSE.md for a complete copy of the license
 *
 * @package     XtractPDF
 * @author      Florida State University iDigInfo (http://idiginfo.org)
 * @copyright   Copyright (C) 2013 Florida State University (http://fsu.edu)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt
 * @link        http://idiginfo.org
 */

// ------------------------------------------------------------------

namespace XtractPDF\Provider;

use Silex\Application;
use Silex\Provider\MonologServiceProvider as BaseMonologServiceProvider;
use Monolog\Logger;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FirePHPHandler;

class MonologServiceProvider extends BaseMonologServiceProvider
{
    public function register(Application $app)
    {
        //Do the parent
        parent::register($app);

        //Extend it with Chrome and Firebug handlers if in debug mode
        $app['monolog'] = $app->share($app->extend('monolog', function($monolog, $app) {
            
            if ($app['debug'] && class_exists('\ChromePhp')) {
                $monolog->pushHandler(new ChromePHPHandler(), Logger::DEBUG);
            }
            if ($app['debug'] && class_exists('\FB')) {
                $monolog->pushHandler(new FirePHPHandler(), Logger::DEBUG);   
            }

            return $monolog;
        }));
    }    
}

/* EOF: MonologServiceProvider.php */