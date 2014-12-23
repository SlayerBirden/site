<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Router;

use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Util\RequestInterface;

class Stack extends AbstractRouter implements RouterInterface
{
    /** @var \SplStack  */
    protected $_routes;

    /**
     * {@inheritdoc}
     */
    public function addRoute(RouteInterface $route, $mode = self::STACK_MODE_APPEND)
    {
        if (self::STACK_MODE_APPEND === $mode) {
            $this->_routes->push($route);
        } elseif (self::STACK_MODE_PREPEND === $mode) {
            $this->_routes->unshift($route);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setRoutes(array $routes)
    {
        // we need to clear stack first
        while ($this->_routes->count() > 0) {
            $this->_routes->pop();
        }
        foreach ($routes as $route) {
            $this->_routes->push($route);
        }
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function clearRoutes()
    {
        $this->_routes = new \SplStack();

        return $this;
    }
}
