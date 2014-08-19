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

class Stack implements RouterInterface
{


    /** @var \SplStack  */
    protected $_routes;

    public function __construct()
    {
        $this->_routes = new \SplStack();
    }

    public function addRoute(RouteInterface $route, $mode = self::STACK_MODE_APPEND)
    {
        if (self::STACK_MODE_APPEND === $mode) {
            $this->_routes->push($route);
        } elseif (self::STACK_MODE_PREPEND === $mode) {
            $this->_routes->unshift($route);
        }
    }

    public function addRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->_routes->push($route);
        }
    }

    public function setRoutes(array $routes)
    {
        // we need to clear stack first
        while($this->_routes->count() > 0) {
            $this->_routes->pop();
        }
        foreach ($routes as $route) {
            $this->_routes->push($route);
        }
    }

    public function match(RequestInterface $request)
    {
        $matched = false;
        foreach ($this->_routes as $route) {
            /** @var RouteInterface $route */
            if ($success = $route->match($request)) {
                $matched = $success;
                break;
            }
        }
        return $matched;
    }

    public function assemble(array $params)
    {
        return;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return;
    }
}
