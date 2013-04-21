<?php

namespace XtractPDF\Controller;

use Silex\Application;
use Upload\File as UploadFile;
use Upload\Validation as UploadVal;
use Upload\Storage\FileSystem as UploadFileSystem;
use Upload\Exception\UploadException;
use RuntimeException, Exception;
use XtractPDF\Library\ExtractorBag;
use SchoalrExtract\Library\ExtractorException;
use Symfony\Component\Stopwatch\Stopwatch;
use XtractPDF\Library\Controller;
use Silex\ControllerCollection;

/**
 * Extractor Controller
 */
class Extractor extends Controller
{
    /**
     * @var  Upload\Storage\FileSystem
     */
    private $uploader;

    /**
     * @var array
     */
    private $extractors;

    /**
     * @var string Filepath of uploads
     */
    private $filepath;

    /**
     * @var Silex_URLGENERATOR
     */
    private $urlGenerator;

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
        $routes->post('/extract',    array($this, 'extractAction'))->bind('extract');
        $routes->get('/pdf/{file}',  array($this, 'renderPdfAction'))->bind('viewpdf');
    }

    // --------------------------------------------------------------

    /**
     * The init method is run upon the controller executing
     *
     * Pull libraries form the DiC here in child classes
     */
    protected function init(Application $app)
    {        
        $this->uploader     = $app['uploader'];
        $this->extractors   = $app['extractors'];
        $this->filepath     = $app['pdf_filepath'];
        $this->urlGenerator = $app['url_generator'];
    }

    // --------------------------------------------------------------

    /**
     * Extract (upload) Action
     *
     * POST /upload {engine=string}
     */
    public function extractAction()
    {        
        $stopwatch = new Stopwatch();

        //Setup a unique key to name and identify the uploaded file
        $key = md5(time() . rand(100000, 999999));

        //Setup the file upload object
        $f = new UploadFile('pdffile', $this->uploader);
        $f->setName($key); //Rename it on upload to our key
        $f->addValidations($this->getValidators()); //Set validations

        //Determine which extractor engine to use ($_POST['engine'])
        $extractor = $this->extractors->get($this->getPostParams('engine') ?: 'poppler');

        if ( ! $extractor) {
            return $this->abort(400, "The specified extractor does not exist");
        }

        //Do the uploads
        try {

            $stopwatch->start('pdfconvert');

            //Process the upload
            $f->upload();

            //Get the filename
            $filename = $f->getNameWithExtension();
            $filepath = $this->filepath. '/' . $filename;

            //DO IT!!       
            $txtOutput = (string) $extractor->extract($filepath);
            $evt = $stopwatch->stop('pdfconvert');

            //Prepare the output
            $output = array(
                'pdfurl'    => $this->urlGenerator->generate('viewpdf', array('file' => $filename)),
                'txt'       => $txtOutput,
                'extractor' => $extractor::getName(),
                'time'      => $evt->getDuration() / 1000
            );

            return $this->json($output);
        }
        catch (UploadException $e) {
            return $this->abort(400, $f->getErrors());
        }
        catch (ExtractorException $e) {
            return $this->abort(500, $e->getMessage());
        }
        catch (Exception $e) {
            return $this->abort(500, "An internal error has occured.");
        }
    }

    // --------------------------------------------------------------

    /**
     * Render a PDF and then destroy it
     *
     * GET /pdf
     * 
     * @param string $file  The filename
     */
    public function renderPdfAction($file)
    {
        //Get the filepath
        $filepath = $this->filepath . '/' . $file;

        //Will remove the file after it is done streaming
        //@TODO: DEBUG THIS... or find another way
        // $app->finish(function() use ($filepath) {
        //     if (file_exists($filepath)) {
        //         unlink($filepath);
        //     }
        // });

        //If the file is readable, then send it; else 404
        if (is_readable($filepath)) {
            return $this->sendFile($filepath);
        }
        else {
            return $this->abort(410, "PDF file gone.  Uploaded PDFs are deleted immediately");
        }
    }

    // --------------------------------------------------------------


    /**
     * Get file upload validators
     *
     * @return array  Array of Upload Validators
     */
    private function getValidators()
    {
        $mimeVal = new UploadVal\Mimetype('application/pdf');
        $sizeVal = new UploadVal\Size('10M');
        $mimeVal->setMessage("The file does not appear to be a PDF");
        return array($mimeVal, $sizeVal);
    }
}

/* EOF: Converter.php */