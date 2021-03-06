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

interface RouterInterface extends RouteInterface
{
    const STACK_MODE_APPEND = 1;
    const STACK_MODE_PREPEND = 2;

    /**
     * @param  RouteInterface $route
     * @param  int $mode
     * @return self
     */
    public function addRoute(RouteInterface $route, $mode = self::STACK_MODE_APPEND);

    /**
     * @param  RouteInterface[] $routes
     * @return self
     */
    public function addRoutes(array $routes);

    /**
     * @param  RouteInterface[] $routes
     * @return self
     */
    public function setRoutes(array $routes);

    /**
     * clear all routes
     * @return self
     */
    public function clearRoutes();
}
