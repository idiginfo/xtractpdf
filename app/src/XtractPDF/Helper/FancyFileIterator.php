<?php

namespace XtractPDF\Helper;

use Iterator, FilterIterator, ArrayIterator, 
    DirectoryIterator, RecursiveDirectoryIterator,
    RecursiveIteratorIterator, SplFileInfo;
use RuntimeException;

/**
 * Fancy File Iterator allows recursive, nonrecursive iterating
 * while also checking extension and path info
 */
class FancyFileIterator extends FilterIterator
{
    /**
     * @var array
     */
    private $allowedExts;

    /**
     * @var array
     */
    private $allowedMimeTypes;

    /**
     * @var Iterator
     */
    private $baseIterator;

    // --------------------------------------------------------------

    public function __construct($path, $recurse, $allowedExts = null, $allowedMimeTypes = null)
    {
        //Check path
        if ( ! is_readable($path)) {
            throw new RuntimeException("Cannot read path: " . $path);
        }

        //Check allowed exts
        if ($allowedExts) {
            $this->allowedExts = array_map(function($v) {
                return strtolower(ltrim($v, '.'));
            }, (array) $allowedExts);
        }
        else {
            $this->allowedExts = array();
        }

        //Check allowed mime-types
        if ($allowedMimeTypes) {
            $this->allowedMimeTypes = array_map(function($v) {
                return strtolower($v);
            }, (array) $allowedMimeTypes);
        }
        else {
            $this->allowedMimeTypes = array();
        }

        //Build the base iterator
        if (is_dir($path)) {
            $baseIterator = ($recurse) 
                ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path))
                : new DirectoryIterator($path);
        }
        else {
            $baseIterator = new ArrayIterator(array(new SplFileInfo($path)));
        }

        //Set base iterator
        parent::__construct($baseIterator);
    }
    // --------------------------------------------------------------

    public function accept()
    {   
        $item = $this->getInnerIterator()->current();
        $itemPath = $item->getRealPath();

        if ($item->getBaseName() == '.' OR $item->getBaseName() == '..') {
            return false;
        }

        //Allowed Extensions
        if ( ! empty($this->allowedExts)) {
            $ext = pathinfo($itemPath, PATHINFO_EXTENSION);
            if ( ! in_array(strtolower($ext), $this->allowedExts)) {
                return false;
            }
        }

        //Allowed Mime-Types
        if ( ! empty($this->allowedMimeTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $itemPath);
            finfo_close($finfo);
            if ( ! in_array(strtolower($mimeType), $this->allowedMimeTypes)) {
                return false;
            }
        }

        //If made it here
        return true;
    }

}


/* EOF: FancyFileIterator.php */