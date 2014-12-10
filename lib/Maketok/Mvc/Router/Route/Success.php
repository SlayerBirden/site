<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router\Route;


class Success
{

    /** @var  RouteInterface */
    protected $_route;

    public function __construct(RouteInterface $route)
    {
        $this->_route = $route;
    }

    /**
     * @return RouteInterface
     */
    public function getMatchedRoute()
    {
        return $this->_route;
    }

    /**
     * @param RouteInterface $route
     * @return $this
     */
    public function setMatchedRoute(RouteInterface $route)
    {
        $this->_route = $route;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->getMatchedRoute()->getParameters();
    }
}
