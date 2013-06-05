<?php

namespace XtractPDF;

use Silex\Application as SilexApp;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\MonologServiceProvider;
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

        //Run it!
        return $this->run();
    }

    // --------------------------------------------------------------

    public function executeCli()
    {
        $consoleApp = new ConsoleApp('XtractPDF');
        $app =& $this;

        //Registration function
        $register = function(Library\Command $command) use ($consoleApp, $app) {
            $command->init($app);
            $consoleApp->add($command);
        };

        //Add commands
        $register(new Command\Extract());

        //Run it
        $consoleApp->run();
    }

    // --------------------------------------------------------------

    protected function basePath($subPath = '')
    {
        $subPath = trim($subPath, '/');
        return realpath(__DIR__ . '/../../../' . $subPath);
    }

    // --------------------------------------------------------------

    private function loadWebLibraries()
    {
        $app =& $this;

        //Mode
        if ($this->mode == self::DEVELOP) {
            $app['debug'] = true;
        }

        //
        // Web Libraries
        //

        //Service Controller
        $app->register(new ServiceControllerServiceProvider());

        //URL Generator
        $app->register(new UrlGeneratorServiceProvider());

        //Twig
        $app->register(new TwigServiceProvider(), array(
            'twig.path' => $this->basePath('/templates')
        ));

        //Uploader (overwrite = true)
        $app['uploader'] = $app->share(function() use ($app) {
            return new UploadFileSystem($this['pdf_filepath'], true);
        });

        //
        // Controllers
        //
        $app->mount('', new Controller\Uploader());
        $app->mount('', new Controller\Workspace());
        $app->mount('', new Controller\PdfViewer());
        $app->mount('', new Controller\MainInterface());
    }

    // --------------------------------------------------------------

    private function loadCommonLibraries()
    {
        $app =& $this;

        //Config
        $app['config'] = new Config($this->basePath('config'));

        //Logfile Path
        $logFilePath = ($app['config']->logpath{0} == '/')
            ? $app['config']->logpath
            : $this->basePath($app['config']->logpath);
        $logFilePath = rtrim($logFilePath, '/') . '/xtractpdf.log';

        //Monolog
        $app->register(new MonologServiceProvider(), array(
            'monolog.name'    => 'xtractpdf',
            'monolog.logfile' => $logFilePath,
            'monolog.level'   => Logger::INFO
        ));

        //$app['mongo'] - DocumentManager
        $this->register(new Provider\DoctrineMongoServiceProvider());

        //Guzzle
        $app['guzzle'] = $app->share(function() use ($app) {
            return new Guzzle\Service\Client();
        });

        //Document Model Factory
        $app['doc_model_factory'] = $app->protect(function($filename) use ($app) {
            return new Model\Document($filename);
        });

        //PDFX Extractor
        $app['extractor'] = $app->share(function() use ($app) {
            return new Extractor\PDFX($app['guzzle']);
        });

        //Filepath
        $app['pdf_filepath'] = $app->share(function() use ($app) {
            return ($app['config']->uploads{0} == '/') 
                ? $app['config']->uploads 
                : $this->basePath($app['config']->uploads);
        });
    }
}

/* EOF: App.php */