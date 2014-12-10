<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template;

abstract class AbstractEngine implements EngineInterface
{

    /**
     * internal handler for real engine
     * @var object
     */
    protected $_engine;

    /**
     * optional method for configuring different engines
     * @param mixed $options
     * @return mixed
     */
    public function configure($options)
    {
        // some special login?
    }
}
