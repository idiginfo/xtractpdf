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

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Silex\Application;
use Silex\ControllerCollection;
use XtractPDF\Helper\ArrayDifferator;
use XtractPDF\Core\Controller;
use XtractPDF\Model;

/**
 * Library Controller
 */
class XtractPDF extends Controller
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
        $routes->get('/xtractpdf',          array($this, 'indexAction'))->bind('xtractpdf');
        $routes->get('/xtractpdf/{uniqId}', array($this, 'singleAction'))->bind('xtractpdf_single');
        
        $routes->post('/xtractpdf',          array($this, 'uploadAction'))->bind('xtractpdf_upload');
        $routes->post('/xtractpdf/{uniqId}', array($this, 'updateAction'))->bind('xtractpdf_update');

        $routes->get('/xtractpdf/pdf/{id}', array($this, 'renderPdfAction'))->bind('viewpdf');
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
        $this->jatsRenderer  = $app['renderers']->get('jats-xml');
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
        $this->viewData['doclist']        = $this->docMgr->listDocuments($limit, $query, $offset);
        $this->viewData['builders']       = $this->builders->getAll();
        $this->viewData['defaultBuilder'] = 'pdfx';

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
            return $this->twig->render('pages/xtractpdf/index.html.twig', $this->viewData);
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

        //If XML, display or download JATS-XML
        if (in_array($this->getQueryParams('response_format'), array('jats', 'xml'))) {

            $headers = array('Content-type' => 'application/xml');
            if ($this->getQueryParams('dl')) {
                $headers['Content-Disposition'] = sprintf('attachment; filename="%s"', $doc->uniqId);
            }
            return $this->customResponse($this->jatsRenderer->serialize($doc), 200, $headers);
        }

        //If JSON, return the document
        elseif ($this->clientExpects('json')) {

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
            return $this->twig->render('pages/xtractpdf/single.html.twig', $this->viewData);
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

        //Build with builder
        if ($this->getPostParams('builder')) {
            $doc = $this->builders->get($this->getPostParams('builder'))->build(
                $this->docMgr->getStreamPdfUri($doc->uniqId), 
                $doc
            );
            $this->docMgr->updateDocument($doc);
        }

        //Return a response
        if ($this->clientExpects('json')) {
            return $this->json(array(
                'doc' => $this->arrayRenderer->render($doc),
                'url' => $this->getSiteUrl('xtractpdf/' . $doc->uniqId),
                'new' => $isNew
            ), $isNew ? 201 : 200);
        }
        else { //Do HTML redirect
            return $this->redirect('/xtractpdf/' . $doc->uniqId);
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
            return $this->redirect('/xtractpdf/' . $uniqId);
        }           
    }

    // --------------------------------------------------------------

    /**
     * Render a PDF by its unique identifier
     *
     * GET /pdf/{id}
     * 
     * @param string $id  Identifier for the document
     */
    public function renderPdfAction($id)
    {
        //If the file is readable, then send it; else 404
        if ($this->docMgr->checkDocumentExists($id)) {
            return $this->stream($this->docMgr->streamPdf($id), 200, array('Content-type' => 'application/pdf'));
        }
        else {
            return $this->abort(404, "Could not find document");
        }
    }        
}    

/* EOF: XtractPDF.php */