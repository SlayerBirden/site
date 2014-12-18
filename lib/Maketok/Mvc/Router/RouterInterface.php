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
use Maketok\Util\ConfigReaderInterface;

interface RouterInterface extends RouteInterface, ConfigReaderInterface
{

    const STACK_MODE_APPEND = 1;
    const STACK_MODE_PREPEND = 2;

    /**
     * @param  RouteInterface $route
     * @param  int            $mode
     * @return mixed
     */
    public function addRoute(RouteInterface $route, $mode = self::STACK_MODE_APPEND);

    /**
     * @param  array $routes
     * @return mixed
     */
    public function addRoutes(array $routes);

    /**
     * @param  array $routes
     * @return mixed
     */
    public function setRoutes(array $routes);

    /**
     * clear all routes
     * @return self
     */
    public function clearRoutes();
}
