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

            $doc = $this->docMgr->getDocument($id);

            //Update metadata
            if ($this->getPostParams('meta')) {
                $this->updateDocMetadata($this->getPostParams('meta'));
            }
            
            //Update authors
            if ($this->getPostParams('authors')) {
                $this->updateDocAuthors($this->getPostParams('authors'));
            }            

            //Update content
            if ($this->getPostParams('content')) {
                $this->updateDocContent($this->getPostParams('content'));
            }            

            //Update citations
            if ($this->getPostParams('citations')) {
                $this->updateDocCitations($this->getPostParams('citations'));
            }       

            //Persist the update
            $this->docMgr->updateDocument($doc);

            //Return a response
            return $this->json(array('message' => 'Updated document', 'id' => $id));
        }
        else {
            return $this->json(array('message' => 'Could not find document', 404));
        }        
    }  

    // --------------------------------------------------------------

    protected function updateDocMetadata(array $data, Model\Document $doc)
    {
        foreach($data as $metaName => $metaValue) {
            $doc->setMeta($metaName, $metaValue);
        }

        return $doc;
    }

    // --------------------------------------------------------------

    protected function updateDocAuthors(array $data, Model\Document $doc)
    {
        $authors = array();

        foreach($data as $authorName) {
            $authors[] = new Model\DocumentAuthor($authorName);
        }

        $doc->setAuthors($authors);
        return $doc;
    }

    // --------------------------------------------------------------

    protected function updateDocContent(array $data, Model\Document $doc)
    {
        $sections = array();

        $data = json_decode($data, true);
        foreach ($data as $sectionTitle => $paragraphs) {
            $sections[] = new Model\DocumentSection($sectionTitle, $paragraphs);
        }

        $doc->setSections($sections);
        return $doc;
    }

    // --------------------------------------------------------------

    protected function updateDocCitations(array $data, Model\Document $doc)
    {
        $citations = array();

        foreach ($data as $citation) {
            $citations[] = new Model\DocumentCitation($citation);
        }

        $doc->setCitations($citations);
        return $doc;
    }

}

/* EOF: Workspace */