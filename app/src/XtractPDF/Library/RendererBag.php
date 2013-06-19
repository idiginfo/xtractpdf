<?php

namespace XtractPDF\Library;

use XtractPDF\DocRenderer\RendererInterface;
use Pimple;

/**
 * Doc Renderer Bag
 */
class RendererBag extends Pimple
{
    /**
     * @param Pimple
     */
    private $bag;

    // --------------------------------------------------------------

    public function __construct(array $renderers = array())
    {
        $this->set($renderers);
    }

    // --------------------------------------------------------------

    public function set(array $renderers)
    {
        $this->bag = new Pimple();
        foreach($renderers as $renderer) {
            $this->add($renderer);
        }
    }   

    // --------------------------------------------------------------

    public function add(RendererInterface $renderer)
    {
        $this->bag[$renderer::getSlug()] = $renderer;
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

/* EOF: RendererBag.php */