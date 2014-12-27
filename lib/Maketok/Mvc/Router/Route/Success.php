<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Router\Route;

class Success
{
    /**
     * @var RouteInterface
     */
    protected $route;

    public function __construct(RouteInterface $route)
    {
        $this->route = $route;
    }

    /**
     * @return RouteInterface
     */
    public function getMatchedRoute()
    {
        return $this->route;
    }

    /**
     * @param  RouteInterface $route
     * @return $this
     */
    public function setMatchedRoute(RouteInterface $route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return callable
     */
    public function getResolver()
    {
        return $this->getMatchedRoute()->getResolver();
    }
}
