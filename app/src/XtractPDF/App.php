<?php

namespace XtractPDF;

use Silex\Application as SilexApp;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use XtractPDF\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Upload\Storage\FileSystem as UploadFileSystem;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Console\Application as ConsoleApp;
use Whoops\Provider\Silex\WhoopsServiceProvider;
use Whoops\Handler\JsonResponseHandler;
use Configula\Config;
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

        //Load CLI Commands
        $consoleApp->add(new Command\DeleteOldFiles($this['pdf_filepath']));

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
        $app->mount('', new Controller\MainInterface());
        $app->mount('', new Controller\Extractor());
        $app->mount('', new Controller\StaticPages());
    }

    // --------------------------------------------------------------

    private function loadCommonLibraries()
    {
        $app =& $this;

        //Config
        $app['config'] = new Config($this->basePath('config'));

        //Filepath
        $app['pdf_filepath'] = $app->share(function() use ($app) {
            return ($app['config']->uploads{0} == '/') 
                ? $app['config']->uploads 
                : $this->basePath($app['config']->uploads);
        });

        //PDF Extractors
        $app['extractors'] = $this->share(function() use ($app) {
            return new Library\ExtractorBag(array(
                new Extractor\PopplerPDFtoTxt(),                
                new Extractor\PDFMiner()

                // IMPELMENT THESE LATER
                // new Extractor\CrossRefExtractor(),
                // new Extractor\LaPDFText(),
            ));
        });
    }
}

/* EOF: App.php */