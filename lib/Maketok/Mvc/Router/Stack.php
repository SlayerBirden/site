<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace Maketok\Mvc\Router;

use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Util\RequestInterface;

class Stack implements RouteInterface
{

    public function addRoute(RouteInterface $route)
    {

    }

    public function addRoutes(array $routes)
    {

    }

    public function setRoutes(array $routes)
    {

    }

    public function removeRoute(RouteInterface $route)
    {

    }

    public function match(RequestInterface $request)
    {
        // TODO: Implement match() method.
    }

    public function assemble(array $params)
    {
        // TODO: Implement assemble() method.
    }
}