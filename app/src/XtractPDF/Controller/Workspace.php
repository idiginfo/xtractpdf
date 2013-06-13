<?php

namespace XtractPDF\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\Stopwatch\Stopwatch;
use XtractPDF\Core\Controller;
use XtractPDF\Model;

/**
 * Workspace Controller
 */
class Workspace extends Controller
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
     * @var XtractPDF\Extractor\ExtractorInterface
     */
    private $extractor;

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
        $routes->get('/workspace/{id}',  array($this, 'renderWorkspaceAction'))->bind('workspace');
        $routes->post('/workspace/{id}', array($this, 'updateDocAjaxAction'))->bind('ws-docupdate');
    }

    // --------------------------------------------------------------

    /**
     * The init method is run upon the controller executing
     *
     * Pull libraries form the DiC here in child classes
     */
    protected function init(Application $app)
    {        
        $this->twig      = $app['twig'];        
        $this->docMgr    = $app['doc_mgr'];
        $this->extractor = $app['extractor'];
    }

    // --------------------------------------------------------------

    /**
     * Render a PDF and then destroy it
     *
     * GET /workspace/{id}
     * 
     * @param string $id  Unique Identifier of the document
     */
    public function renderWorkspaceAction($id)
    {
        //If the file is readable, then send it; else 404
        if ($this->docMgr->checkDocumentExists($id)) {

            $doc = $this->docMgr->getDocument($id);

            if ( ! $doc->isExtracted) {

                //@TODO: THIS IS A HACK -- get the stream of the PDF, and put it into a data string
                //Adapt the Helper/StreamCallbackWrapper to dynamically build a stream instead
                ob_start();
                $this->docMgr->streamPdf($id)->__invoke();
                $pdfContent = ob_get_clean();
                $data = 'data://application/pdf;base64,' . base64_encode($pdfContent);

                //Run the PDFX converter to get the XML
                $result = $this->extractor->extract($data);

                //Process XML into data model
                $doc = $this->extractor->map($result, $doc);

                //Mark it as extracted and save it
                $doc->markExtracted();
                $this->docMgr->updateDocument($doc);
            }
            
            //Process data model into Twig View
            return $this->twig->render('p_workspace.html.twig', array('doc' => $doc));
        }
        else {
            return $this->abort(404, "Could not find document");
        }
    }    

    // --------------------------------------------------------------

    /**
     * Render a PDF and then destroy it
     *
     * POST /workspace/{id}
     * 
     * @param string $id  Unique Identifier of the document
     */  
    public function updateDocAjaxAction($id)
    {
        //If the file is readable, then send it; else 404
        if ($this->docMgr->checkDocumentExists($id)) {

            //Get the doc
            $doc = $this->docMgr->getDocument($id);

            //404 if no doc
            if ( ! $doc) {
                return $this->json(array('message' => 'Could not find document'), 404);
            }      

            //Mark complete (if doing that) or submit content (if doing that)
            if ($this->getPostParams('mark')) {
                $doc->markComplete($this->getPostParams('isComplete'));                
            }
            else {

                $meta      = $this->getPostParams('meta');
                $authors   = array_filter($this->getPostParams('authors'));
                $citations = array_filter($this->getPostParams('citations'));
                $content   = json_decode($this->getPostParams('content'));
                $abstract  = json_decode($this->getPostParams('abstract'));

                $this->debug($abstract);

                //Missing anything?
                if ( ! is_array($abstract) OR ! is_array($meta) OR ! is_array($authors) OR ! is_array($citations) OR ! is_array($content)) {
                    return $this->json(array('message' => "Invalid submission"), 400);
                }

                //Set meta
                foreach($meta as $k => $v) {
                    $doc->setMeta($k, $v);
                }

                //Set authors
                $authorsArr = array();
                foreach($authors as $auth) {
                    $authorsArr[] = new Model\DocumentAuthor($auth);
                }
                $doc->setAuthors($authorsArr);

                //Set citations
                $citationsArr = array();
                foreach($citations as $cite) {
                    $citationsArr[] = new Model\DocumentCitation($cite);
                }
                $doc->setCitations($citationsArr);

                //Set Abstract
                $abstractObj = new Model\DocumentAbstract();
                foreach($abstract as $sec) {
                    $secObj = new Model\DocumentSection($sec->title);
                    foreach(array_filter($sec->paragraphs) as $para) {
                        $secObj->addParagraph(new Model\DocumentParagraph($para));
                    }
                    $abstractObj->addSection($secObj);
                }
                $doc->setAbstract($abstractObj);                

                //Set content
                $contentObj = new Model\DocumentContent();
                foreach($content as $sec) {
                    $secObj = new Model\DocumentSection($sec->title);
                    foreach(array_filter($sec->paragraphs) as $para) {
                        $secObj->addParagraph(new Model\DocumentParagraph($para));
                    }
                    $contentObj->addSection($secObj);
                }
                $doc->setContent($contentObj);
            }

            //Update
            $this->docMgr->updateDocument($doc);

            //Result
            return $this->json(array('message' => 'Updated document', 'markedComplete' => $doc->isComplete), 200);
        }
    }  
}

/* EOF: Workspace */