<?php

namespace XtractPDF\Controller;

use Silex\Application;
use Upload\File as UploadFile;
use Upload\Validation as UploadVal;
use Upload\Storage\FileSystem as UploadFileSystem;
use Upload\Exception\UploadException;
use RuntimeException, Exception;
use Symfony\Component\Stopwatch\Stopwatch;
use XtractPDF\Library\Controller;
use Silex\ControllerCollection;

/**
 * Uploader Controller
 */
class Uploader extends Controller
{
    /**
     * @var  Upload\Storage\FileSystem
     */
    private $uploader;

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
        $routes->post('/upload',    array($this, 'uploadAction'))->bind('upload');
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
        $this->filepath     = $app['pdf_filepath'];
        $this->urlGenerator = $app['url_generator'];
    }

    // --------------------------------------------------------------

    /**
     * Upload PDF Action
     *
     * POST /upload {engine=string}
     */
    public function uploadAction()
    {        
        $stopwatch = new Stopwatch();

        //Setup a unique key to name and identify the uploaded file
        $key = md5(time() . rand(100000, 999999));

        //Setup the file upload object
        $f = new UploadFile('pdffile', $this->uploader);
        $f->setName($key); //Rename it on upload to our key
        $f->addValidations($this->getValidators()); //Set validations

        //Do the uploads
        try {

            $stopwatch->start('pdfconvert');

            //Process the upload
            $f->upload();

            //Get the filename
            $filename = $f->getNameWithExtension();
            $filepath = $this->filepath. '/' . $filename;

            $evt = $stopwatch->stop('pdfconvert');

            //Prepare the output
            $output = array(
                'pdfurl'    => $this->urlGenerator->generate('viewpdf',   array('file' => $filename)),
                'wsurl'     => $this->urlGenerator->generate('workspace', array('file' => $filename)),
                'time'      => $evt->getDuration() / 1000
            );

            //Log it
            $this->log('info', sprintf(
                'PDF  Uploaded: %s -- %s',
                $key,
                $filename
            ));

            //Output
            return $this->json($output);
        }
        catch (UploadException $e) {
            return $this->abort(400, $f->getErrors());
        }
        catch (Exception $e) {
            return $this->abort(500, sprintf("An internal error has occured. (%s)", $e->getMessage()));
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
        //Size Validator
        $sizeVal = new UploadVal\Size('5M');

        //Mime Validator
        $mimeVal = new UploadVal\Mimetype('application/pdf');
        $mimeVal->setMessage("The file does not appear to be a PDF");

        //The end
        return array($mimeVal, $sizeVal);
    }
}

/* EOF: Converter.php */