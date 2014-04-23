<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Mvc\Controller;

use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Mvc\Router\Stack;
use Maketok\Observer\StateInterface;

class Front
{



    public function dispatch(StateInterface $state)
    {
        if ($success = $this->_getRouter()->match($state->request)) {
            // TODO load controller based on success route
        }
    }

    /**
     * @return RouteInterface
     */
    protected function _getRouter()
    {
        return new Stack();
    }
}