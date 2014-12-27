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
    protected $routes;

    /**
     * {@inheritdoc}
     */
    public function addRoute(RouteInterface $route, $mode = self::STACK_MODE_APPEND)
    {
        if (self::STACK_MODE_APPEND === $mode) {
            $this->routes->push($route);
        } elseif (self::STACK_MODE_PREPEND === $mode) {
            $this->routes->unshift($route);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function match(RequestInterface $request)
    {
        $matched = false;
        foreach ($this->routes as $route) {
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
        $this->routes = new \SplStack();
        return $this;
    }
}
