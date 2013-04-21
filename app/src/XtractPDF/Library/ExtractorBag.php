<?php

namespace XtractPDF\Library;

use XtractPDF\Extractor\ExtractorInterface;
use IteratorAggregate, Countable, ArrayIterator;
use LogicException;

/**
 * Extractor Bag
 */
class ExtractorBag implements IteratorAggregate, Countable
{
    private $objs;

    /**
     * @param XtractPDF\Extractor\ExtractorInterface
     */
    private $default;

    // --------------------------------------------------------------
    
    public function __construct(array $extractors = array()) {
        $this->objs = array();
        $this->setAll($extractors);
    }

    // --------------------------------------------------------------

    public function add(ExtractorInterface $extractor)
    {
        $this->objs[$extractor::getSlug()] = $extractor;
    }

    // --------------------------------------------------------------

    public function setAll(array $extractors = array())
    {
        foreach($extractors as $extractor) {
            $this->add($extractor);
        }
    }

    // --------------------------------------------------------------

    public function get($extractorSlug)
    {
        return ($this->has($extractorSlug)) ? $this->objs[$extractorSlug] : null;
    }

    // --------------------------------------------------------------

    public function has($extractorSlug)
    {
        return isset($this->objs[$extractorSlug]);
    }

    // --------------------------------------------------------------

    public function remove($extractorSlug)
    {
        if ($this->has($extractorSlug)) {
            unset($this->objs[$extractorSlug]);
        }
    }

    // --------------------------------------------------------------

    /**
     * Array of metadata about each extractor
     *
     * @return array
     */
    public function getExtractorInfo()
    {
        $output = array();

        foreach($this as $ext) {
            $output[$ext::getSlug()] = array(
                'name'        => $ext::getName(),
                'link'        => $ext::getLink(),
                'description' => $ext::getDescription()
            );
        }

        return $output;
    }

    // --------------------------------------------------------------

    /**
     * Returns an iterator for attributes.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new ArrayIterator($this->objs);
    }

    // --------------------------------------------------------------

    /**
     * Returns the number of extractors.
     *
     * @return int The number of extractors
     */
    public function count()
    {
        return count($this->objs);
    }    
}

/* EOF: ExtractorBag.php */