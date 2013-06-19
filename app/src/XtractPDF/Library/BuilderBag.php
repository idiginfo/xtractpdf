<?php

namespace XtractPDF\Library;

use XtractPDF\DocBuilder\BuilderInterface;
use Pimple;

/**
 * Doc Builder Bag
 */
class BuilderBag extends Pimple
{
    /**
     * @param Pimple
     */
    private $bag;

    // --------------------------------------------------------------

    public function __construct(array $builders = array())
    {
        $this->set($builders);
    }

    // --------------------------------------------------------------

    public function set(array $builders)
    {
        $this->bag = new Pimple();
        foreach($builders as $builder) {
            $this->add($builder);
        }
    }   

    // --------------------------------------------------------------

    public function add(BuilderInterface $builder)
    {
        $this->bag[$builder::getSlug()] = $builder;
    }

    // --------------------------------------------------------------

    public function getAll()
    {
        $out = array();

        foreach($this->bag->keys() as $k) {
            $out[$k] = $this->bag[$k];
        }

        return $out;
    }

    // --------------------------------------------------------------

    public function get($slug)
    {
        return $this->bag[$slug];
    }
}

/* EOF: BuilderBag.php */