<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace Maketok\Mvc\Router;

use Maketok\Observer;

class Standard implements RouterInterface
{
    public function dispatch(Observer\State $state)
    {
        var_dump($state); die;
    }

    public function match()
    {
        // TODO: Implement match() method.
    }

    public function assemble()
    {
        // TODO: Implement assemble() method.
    }
}