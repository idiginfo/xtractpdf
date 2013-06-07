<?php

namespace XtractPDF\Controller;

use Silex\Application;
use Upload\File as UploadFile;
use Upload\Validation as UploadVal;
use Upload\Storage\FileSystem as UploadFileSystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Upload\Exception\UploadException;
use RuntimeException, Exception;
use XtractPDF\Core\Controller;
use Silex\ControllerCollection;

/**
 * Uploader Controller
 */
class Uploader extends Controller
{
    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var Silex_URLGENERATOR
     */
    private $urlGenerator;

    /**
     * @var XtractPDF\Library\DocumentMgr
     */
    private $docMgr;

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
        $this->request      = $app['request'];
        $this->urlGenerator = $app['url_generator'];
        $this->docMgr       = $app['doc_mgr'];
    }

    // --------------------------------------------------------------

    /**
     * Upload PDF Action
     *
     * POST /upload
     */
    public function uploadAction()
    {
        $fileInfo = $this->request->files->get('pdffile');

        //Validation - Ensure file was uploaded
        if ( ! $fileInfo instanceOf UploadedFile) {
            return $this->json(array('msg' => 'No file uploaded!'), 400);
        }

        //Validation - ensure it is a PDF file
        if ($fileInfo->getMimeType() != 'application/pdf') {
            return $this->json(array('msg' => 'Only PDF files allowed'), 400);
        }

        //Get the MD5 to use as a unique ID
        $md5 = md5($fileInfo->getRealPath());

        //Create the document (returns false if already exists)
        $doc = $this->docMgr->createDocument($md5, $fileInfo->getRealPath());
        
        //Log upload
        if ($doc) {
            $this->log('info', sprintf('Uploaded new document: %s', $md5));
        }
        else { //if it already exists, then set notice that it exists and get that object
            //@TODO: SET NOTICE HERE...
            $doc = $this->docMgr->getDocument($md5);
            $this->log('info', sprintf('Attempted to upload existing document: %s', $md5));
        }

        //Remove the temporary uploaded file (does PHP do this autmoatically?)
        unlink($fileInfo->getRealPath());

        $output = array(
            'pdfurl' => $this->urlGenerator->generate('viewpdf',   array('id' => $md5)),
            'wsurl'  => $this->urlGenerator->generate('workspace', array('id' => $md5))
        );

        return $this->json($output, 201);
    }
}

/* EOF: Uploader.php */