<?php

namespace XtractPDF\Library;

use XtractPDF\Builder\BuilderInterface;
use Pimple;

class BuilderBag extends Pimple
{
    /**
     * @param Pimple
     */
    private $bag;

    // --------------------------------------------------------------

    public function __construct(array $builders = array())
    {
        $this->setBuilders($builders);
    }

    // --------------------------------------------------------------

    public function setBuilders($builders)
    {
        $this->bag = new Pimple();
        foreach($builders as $builder) {
            $this->addBuilder($builder);
        }
    }   

    // --------------------------------------------------------------

    public function addBuilder(BuilderInterface $builder)
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

    public function get($slug = null)
    {
        return $this->bag[$slug];
    }
}

/* EOF: BuilderBag.php */