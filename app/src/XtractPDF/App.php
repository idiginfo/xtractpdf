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

namespace XtractPDF;

use Silex\Application as SilexApp;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use XtractPDF\Provider\MonologServiceProvider;
use XtractPDF\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Upload\Storage\FileSystem as UploadFileSystem;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Console\Application as ConsoleApp;
use Whoops\Provider\Silex\WhoopsServiceProvider;
use Whoops\Handler\JsonResponseHandler;
use Configula\Config;
use Monolog\Logger;
use Guzzle;
use Pimple;

/**
 * Main App File
 */
class App extends SilexApp
{
    const PRODUCTION = 1;
    const DEVELOP    = 2;

    // --------------------------------------------------------------

    /**
     * @var int  DEVELOP or PRODUCTION
     */
    private $mode;

    // --------------------------------------------------------------

    public static function main($mode = self::PRODUCTION)
    {
        $cls = get_called_class();
        $obj = new $cls($mode);

        return (php_sapi_name() == 'cli')
            ? $obj->executeCli()
            : $obj->executeWeb();
    }

    // --------------------------------------------------------------

    /**
     * Constructor
     * 
     * @param int $mode  self::PRODUCTION or self::DEVELOP
     */
    public function __construct($mode = self::PRODUCTION)
    {
        parent::__construct();

        //Mode
        $this->mode = (int) $mode;

        //Load libraries
        $this->loadCommonLibraries();
    }

    // --------------------------------------------------------------

    /**
     * Main Execute Method
     */
    public function executeWeb()
    {
        //Load Web Libraries
        $this->loadWebLibraries();        

        //Set Whoops Debugger - Causes problems, so turn it off for now
        // if($this['debug']) {
        //     $this->register(new WhoopsServiceProvider());
        //     $this['whoops']->pushHandler(new JsonResponseHandler());
        // }

        $this['audit_logger']->setContext(array('interface' => 'web'));

        //Run it!
        return $this->run();
    }

    // --------------------------------------------------------------

    public function executeCli()
    {
        $consoleApp = new ConsoleApp('XtractPDF');
        $app =& $this;

        $this['audit_logger']->setContext(array('interface' => 'web'));

        //Registration function
        $register = function(Core\Command $command) use ($consoleApp, $app) {
            $command->init($app);
            $consoleApp->add($command);
        };

        //Add commands
        $register(new Command\Info());
        $register(new Command\DocsBuild());
        $register(new Command\DocsList());
        $register(new Command\DocsDelete());
        $register(new Command\DocsClear());
        $register(new Command\DocsRender());
        $register(new Command\DocsRenderAll());

        //Run it
        $consoleApp->run();
    }

    // --------------------------------------------------------------

    protected function resolvePath($path = '')
    {
        //If not absolute path, precede with basepath
        if ($path{0} != '/') {
            $path = realpath(__DIR__ . '/../../../') . '/' . ltrim($path, '/');
        }

        return rtrim($path, '/');
    }

    // --------------------------------------------------------------

    private function loadWebLibraries()
    {
        $app =& $this;

        //
        // Web Libraries
        //


        //Service Controller
        $app->register(new ServiceControllerServiceProvider());

        //URL Generator
        $app->register(new UrlGeneratorServiceProvider());

        //Twig
        $app->register(new TwigServiceProvider(), array(
            'twig.path' => $this->resolvePath('templates')
        ));

        //
        // If deploy mode is 'demo', replace the document manager with session limited version
        //
        if ($app['config']->deploymode == 'demo') {
            //$app['session']
            $app->register(
                new SessionServiceProvider(), array('session.storage.options' => array(
                    'name'            => 'XtractPDF', 
                    'cookie_lifetime' => $app['config']->session_expire ?: 7200
                )
            ));

            //$app['doc_mgr']
            $app['doc_mgr'] = $app->share(function() use ($app) {
                return new Library\SessionLimitedDocumentMgr(
                    $app['mongo'], 
                    new PdfDataHandler\FilePdfHandler($app['pdf_filepath']),
                    $app['audit_logger'],
                    $app['session']
                );
            });
        }

        //
        // Controllers
        //
        $app->mount('', new Controller\Library());
        $app->mount('', new Controller\About());                       
    }

    // --------------------------------------------------------------

    private function loadCommonLibraries()
    {
        $app =& $this;
        
        //Mode
        if ($this->mode == self::DEVELOP) {
            $app['debug'] = true;
        }
        
        //Config
        $app['config'] = new Config($this->resolvePath('config'));

        //Monolog
        $app->register(new MonologServiceProvider(), array(
            'monolog.name'    => 'xtractpdf',
            'monolog.logfile' => $app['debug'] == true ? $this->resolvePath($app['config']->logpath) . '/debug.log' : $this->resolvePath($app['config']->logpath) . '/xtractpdf.log',
            'monolog.level'   => $app['debug'] == true ? Logger::DEBUG : Logger::ERROR
        ));

        //$app['mongo'] - DocumentManager
        $this->register(new Provider\DoctrineMongoServiceProvider());

        //Upload Filepath
        $app['pdf_filepath'] = $this->resolvePath($app['config']->uploads);

        //API Data Handler
        $app['api_builder'] = $app->share(function() use ($app) {
            return new Library\DocumentAPIHandler();
        });

        //Document Builders
        $app['builders'] = $app->share(function() use ($app) {
            return new Library\BuilderBag(array(
                new DocBuilder\PDFX(new Guzzle\Service\Client()),
                new DocBuilder\Blank()
            ));
        });

        //Document Renderers
        $app['renderers'] = $app->share(function() use ($app) {
            return new Library\RendererBag(array(
                new DocRenderer\ArrayRenderer(),
                new DocRenderer\JatsXmlRenderer()
            ));
        });

        //Audit Log Manager
        $app['audit_logger'] = $app->share(function() use ($app) {
            return new Library\AuditLogger($app['mongo']);
        });

        //Document Manager
        $app['doc_mgr'] = $app->share(function() use ($app) {
            return new Library\DocumentMgr(
                $app['mongo'], 
                new PdfDataHandler\FilePdfHandler($app['pdf_filepath']),
                $app['audit_logger']
            );
        });

    }
}

/* EOF: App.php */