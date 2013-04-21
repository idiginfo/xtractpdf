<?php

namespace XtractPDF;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\ProcessBuilder;
use Upload\Storage\FileSystem as UploadFileSystem;
use Upload\File as UploadFile;
use Upload\Validation as UploadVal;
use Exception;

// ------------------------------------------------------------------

require(__DIR__ . '/app/vendor/autoload.php');

App::main(App::DEVELOP);