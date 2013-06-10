<?php

namespace XtractPDF\Helper;

/**
 * Stream Callback Wrapper
 *
 * @TODO: Re-adapt this pattern for streaming from an object...
 *
 * Adapted from http://www.php.net/manual/en/class.streamwrapper.php#105872
 */
class StreamCallbackWrapper
{
    const WRAPPER_NAME = 'callback'; 

    // --------------------------------------------------------------

    /**
     * @var object
     */
    public $context;

    /**
     * var string
     */ 
    private $_cb; 

    /**
     * @var boolean
     */
    private $_eof = false; 

    // --------------------------------------------------------------

    /**
     * @var boolean  Is registered flag
     */
    private static $_isRegistered = false; 

    // --------------------------------------------------------------

    /**
     * @param  array|string  Callback (eg. "array($obj, 'someMethod')" )
     * @return resource      A stream context resource
     */
    public static function getContext($cb) 
    { 
        //Register the stream wrapper
        if ( ! self::$_isRegistered) { 
            stream_wrapper_register(self::WRAPPER_NAME, get_class()); 
            self::$_isRegistered = true; 
        } 

        if ( ! is_callable($cb)) {
            return false; 
        }

        return stream_context_create(array(self::WRAPPER_NAME => array('cb' => $cb))); 
    } 

    // --------------------------------------------------------------

    public function stream_open($path, $mode, $options, &$opened_path) 
    { 
        //Read only
        if ( ! preg_match('/^r[bt]?$/', $mode) || !$this->context) {
            return false; 
        }

        //Get the context
        $opt = stream_context_get_options($this->context); 

        //Determine the callback
        if (!is_array($opt[self::WRAPPER_NAME]) || 
            !isset($opt[self::WRAPPER_NAME]['cb']) || 
            !is_callable($opt[self::WRAPPER_NAME]['cb'])) return false; 

        $this->_cb = $opt[self::WRAPPER_NAME]['cb']; 

        return true; 
    } 

    // --------------------------------------------------------------

    public function stream_read($count) 
    { 
        if ($this->_eof || !$count) return ''; 

        if (($s = call_user_func($this->_cb, $count)) == '') {
            $this->_eof = true;
        }

        return $s;
    } 

    // --------------------------------------------------------------

    public function stream_eof() 
    { 
        return $this->_eof;
    } 
}

/* EOF: StreamCallbackWrapper.php */