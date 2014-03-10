<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace Maketok\Mvc\Router;

class Standard
{
    public function dispatch(\Maketok\Observer\State $state)
    {
        var_dump($state); die;
    }
}