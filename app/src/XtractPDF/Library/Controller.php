<?php

namespace XTractPDF\Library;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base controller
 */
abstract class Controller implements ControllerProviderInterface
{
    /**
     * @var Silex\Application
     */
    private $app;

    // --------------------------------------------------------------s

    /**
     * Connect method
     *
     * @param Silex\Application $app
     */
    public function connect(Application $app)
    {
        //Setup the app as a controller variable
        $this->app = $app;

        //Get controllers factory
        $routes = $app['controllers_factory'];

        //Call setRoutes from the child class to set the routes
        $this->setRoutes($routes);

        //Add init as before hook
        $app->before(array($this, 'initialize'));

        //Register the routes
        return $routes;
    }

    // --------------------------------------------------------------s

    /**
     * Init method is run as before() hook and runs the protected
     * init() method
     */
    final public function initialize()
    {
        $ob = $this->app['request']->attributes->get('_controller');

        if ($this instanceOf $ob[0]) {
            $this->init($this->app);    
        }        
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
    abstract protected function setRoutes(ControllerCollection $routes);  

    // --------------------------------------------------------------

    /**
     * The init method is run upon the controller executing
     *
     * Pull libraries form the DiC here in child classes
     */
    protected function init(Application $app)
    {        
        //Do nothing; this is meant to be overridden
    }

    // --------------------------------------------------------------   

    /**
     * Log a message to Monolog
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     * @param boolean  Whether the record has been processed
     */
    protected function log($level, $message, array $context = array())
    {
        $method = 'add' . ucfirst(strtolower($level));
        if (method_exists($this->app['monolog'], $method)) {
            return call_user_func(array($this->app['monolog'], $method), $message, $context);
        }
        else {
            throw new \InvalidArgumentException(sprintf(
                "The logging level '%s' is invalid. See Monolog documentation for valid log levels",
                $level
            ));
        }
    }    

    // --------------------------------------------------------------   

    /**
     * Shortcut for debugging
     *
     * @param mixed $item
     * @param string $msg  Optional, if $item is not a string
     */
    protected function debug($item, $msg = null)
    {
        if (is_string($item)) {
            return $this->log('debug', $item);
        }
        else {
            return $this->log('debug', $msg ?: 'Debug', (array) $item);
        }
    }

    // --------------------------------------------------------------   

    /**
     * Get query string parameters from input
     *
     * @param string|null $which
     * @return array|mixed|null
     */
    protected function getQueryParams($which = null)
    {
        return ( ! is_null($which))
            ? $this->app['request']->query->get($which)
            : $this->app['request']->query->all();
    }

    // --------------------------------------------------------------

    /**
     * Get post parameters from input
     *
     * @param string|null $which
     * @return array|mixed|null
     */
    protected function getPostParams($which = null)
    {
        return ( ! is_null($which))
            ? $this->app['request']->request->get($which)
            : $this->app['request']->request->all();
    }

    // --------------------------------------------------------------

    /**
     * Abort
     *
     * @param int $code
     * @param array|string $message
     */
    protected function abort($code, $messages)
    {
        //Do JSON abort
        if ($this->clientExpects('json')) {
            return $this->app->json(array('messages' => (array) $messages), $code);
        }

        //Else - Do normal abort
        if (is_array($messages)) {
            $message = json_encode($messages);
        }

        return $this->app->abort($code, $messages);
    }

    // --------------------------------------------------------------

    /**
     * Redirect to another path in the app
     *
     * @param string $path
     * @return  Redirection (halts app and redirects)
     */
    protected function redirect($path)
    {
        //Ensure left slash
        $path = '/' . ltrim($path, '/');

        //Do it
        return $this->app->redirect($this->getSiteUrl() . $path);
    }

    // --------------------------------------------------------------

    /**
     * Shortcut function for sendFile
     */
    protected function sendFile($file, $status = 200, $headers = array(), $contentDisposition = null)
    {
        return $this->app->sendfile($file, $status, $headers, $contentDisposition);
    }

    // --------------------------------------------------------------

    /**
     * Shortcut function for JSON
     *
     * @return string
     */
    protected function json($output, $code = 200, $headers = array())
    {
        return $this->app->json($output, $code, $headers);
    }

    // --------------------------------------------------------------

    /**
     * Is the request from XmlHttp?
     *
     * @return boolean
     */
    protected function isAjax()
    {
        return $this->app['request']->isXmlHttpRequest();
    }

    /**
     * Check if the client expects a certain content-type
     *
     * e.g.  if ($this->clientExpects('application/json'))
     *       ...
     *
     * Can use shorthand: 'html', 'json', 'xml'
     * 
     * @param  string|array $mimeType
     * @param  boolean      $strict  If false, then *\/* will always return true
     * @return boolean
     */
    protected function clientExpects($mimeType, $strict = true)
    {
        //Shorthand mappings
        $mappings = array(
            'json'  => array('application/json'),
            'html'  => array('text/html', 'application/xhtml+xml'),
            'xml'   => array('application/xml')
        );

        //Using shorthand query parameter override?
        $override = $this->getQueryParams('response_format');
        if ($override && in_array($override, array_keys($mappings))) {
            return true;
        }
        else {
            
            //Using shorthand?
            $expected = (isset($mappings[$mimeType])) 
                ? $mappings[$mimeType] : (array) $mimeType;

            //Client Accept Headers
            $accepted = $this->app['request']->getAcceptableContentTypes();

            //TODO: I SHOULD BE ABLE TO REMOVE THIS.  WTF IS GOING ON BELOW??
            return count(array_intersect($expected, $accepted)) > 0;

            //Return results
            return ($strict)
                ? count(array_intersect($expected, $accepted)) > 0
                : count(array_intersect($expected, $accepted)) > 0 OR in_array('*/*', $accepted);

        }
    }      
}

/* EOF: Controller.php */