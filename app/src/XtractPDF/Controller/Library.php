<?php

namespace XtractPDF\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Silex\Application;
use Silex\ControllerCollection;
use XtractPDF\Helper\ArrayDifferator;
use XtractPDF\Core\Controller;
use XtractPDF\Model;

/**
 * Library Controller
 */
class Library extends Controller
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

    /**
     * @var XtractPDF\Library\DocumentAPIHandler
     */
    private $apiHandler;

    /**
     * @var XtractPDF\DocRenderer\ArrayRenderer
     */
    private $arrayRenderer;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var array
     */
    private $viewData;

    // --------------------------------------------------------------

    /**
     * Constructor - Set Initial State
     */
    public function __construct()
    {
        $this->viewData = array();
    }

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
        $routes->get('/',                 array($this, 'indexAction'))->bind('front');
        $routes->get('/library',          array($this, 'indexAction'))->bind('library');
        $routes->get('/library/{uniqId}', array($this, 'singleAction'))->bind('library_single');
        
        $routes->post('/library',          array($this, 'uploadAction'))->bind('library_upload');
        $routes->post('/library/{uniqId}', array($this, 'updateAction'))->bind('library_update');
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
        $this->twig          = $app['twig'];        
        $this->docMgr        = $app['doc_mgr'];
        $this->builders      = $app['builders'];
        $this->arrayRenderer = $app['renderers']->get('array');
        $this->apiHandler    = $app['api_builder'];
        $this->request       = $app['request'];
    }

    // --------------------------------------------------------------

    /**
     * GET /
     * GET /library
     */
    public function indexAction()
    {
        //Determine limit, offset, search params
        $limit  = $this->getQueryParams('limit')  ?: null;
        $offset = $this->getQueryParams('offset') ?: 0;
        $query  = $this->getQueryParams('query')  ?: null;

        //Get list of items from the docMgr
        $this->viewData['doclist'] = $this->docMgr->listDocuments($limit, $query, $offset);

        //Render response
        if ($this->clientExpects('json')) {

            //Prepare result
            $arr = array();
            foreach($this->viewData['doclist'] as $doc) {
                $arr[] = $this->arrayRenderer->render($doc);
            }

            //Send it
            return $this->json(array('docs' => $arr));
        }
        else { //Do HTML
            return $this->twig->render('pages/library-index.html.twig', $this->viewData);
        }
    }

    // --------------------------------------------------------------
    
    /**
     * GET /library/{uniqId}
     *
     * @param $uniqId  The identifier for a document object
     */
    public function singleAction($uniqId)
    {
        //Get the item from the database (or 404 if it doesn't exist)        
        $doc = $this->docMgr->getDocument($uniqId);

        //If not found, abort
        if ( ! $doc) {
            return $this->abort(404, 'Document Not Found');
        }

        //Display options - for building views with JS and HTML
        $dispOptions = array(
            'availSecTypes'  => Model\DocumentSection::getAllowedTypes(),
            'biblioMetaDisp' => Model\DocumentBiblioMeta::getDispInfo()            
        );      

        //If JSON, return the document
        if ($this->clientExpects('json')) {

            $jsonData = array();
            $jsonData['document'] = $this->arrayRenderer->render($doc);

            if ($this->getQueryParams('disp_opts')) {
                $jsonData['dispOptions'] = $dispOptions;
            }

            return $this->json($jsonData);
        }
        else { //Load the interface

            //Add doc to the viewData
            $this->viewData['doc']         = $doc;
            $this->viewData['docUrl']      = $this->getCurrentUrl();
            $this->viewData['dispOptions'] = $dispOptions;

            //Set the page class for twig views
            $this->viewData['page_class'] = 'workspace';
            return $this->twig->render('pages/library-single.html.twig', $this->viewData);
        }
    }    

    // --------------------------------------------------------------
    
    /**
     * POST /library
     */
    public function uploadAction()
    {
        //Upload the document
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
        $md5 = md5_file($fileInfo->getRealPath());

        //Create the document (returns false if already exists)
        if ($doc = $this->docMgr->createDocument($md5, $fileInfo->getRealPath())) {
            $isNew = true;
        }
        else {
            $doc   = $this->docMgr->getDocument($md5);
            $isNew = false;
        }

        //@TODO: PDFX AUTO-EXTRACTION HERE.....

        //Return a response
        if ($this->clientExpects('json')) {
            return $this->json(array(
                'doc' => $this->arrayRenderer->render($doc),
                'url' => $this->getSiteUrl('library/' . $doc->uniqId),
                'new' => $isNew
            ), $isNew ? 201 : 200);
        }
        else { //Do HTML redirect
            return $this->redirect('/library/' . $doc->uniqId);
        }        
    }

    // --------------------------------------------------------------

    /**
     * POST /library/{uniqId}
     *
     * @param $uniqId  The identifier for a document object
     */
    public function updateAction($uniqId)
    {
        //Check if document exists, otherwise render a 404
        $doc = $this->docMgr->getDocument($uniqId);

        //If not found, abort
        if ( ! $doc) {
            return $this->abort(404, 'Document Not Found');
        }

        //Check for expectd POST data
        $postData = $this->getPostParams('document');

        if ( ! $postData) {
            return $this->abort(400, 'Missing required request parameters');
        }

        //Make a snapshot of the original document before updating
        //@TODO: Move this into documentMgr?
        $origDoc = $this->arrayRenderer->render($doc);

        //Set document data from POST request
        $doc = $this->apiHandler->build($postData, $doc);

        //Compute the diff
        //@TODO: Move this into documentMgr?
        $diff = ArrayDifferator::recursiveDiff(
            $origDoc,
            $this->arrayRenderer->render($doc)
        );

        //Persist document
        $this->docMgr->updateDocument($doc, $diff);

        //Return a response
        if ($this->clientExpects('json')) {

            //Return JSON notification that everything went well and URL pointer to the document
            return $this->json(array(
                'message' => 'Updated Document',
                'docUrl'  => $this->getCurrentUrl()
            ));
        }
        else { //Do HTML redirect
            return $this->redirect('/single/' . $uniqId);
        }           
    }
}    

/* EOF: Library.php */