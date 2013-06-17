<?php

namespace XtractPDF\DocBuilder;

use XtractPDF\Model;

/**
 * PostRequest Document Builder
 */
class PostRequest implements BuilderInterface
{
    /**
     * @return string  A machine-readable name (alpha-dash)
     */
    public static function getSlug()
    {
        return 'post-request';
    }

    // --------------------------------------------------------------
    
    /**
     * @return string  A human-friendly name
     */
    public static function getName()
    {
        return 'POST Request';
    }

    // --------------------------------------------------------------
    
    public static function getDescription()
    {
        return 'Builds document from POST request';
    }

    // --------------------------------------------------------------   

    /**
     * Build a model from the raw PDF data
     *
     * @param  string $stream  Stream or filepath
     * @return string|boolean  Serialized version of extracted data (false upon fail)
     */
    public function build($stream, Model\Document $doc)
    {
        $postData = file_get_contents($stream);
        var_dump("HOORAY!");
        var_dump($postData); die();
    }
}

/* EOF: PostRequest.php */