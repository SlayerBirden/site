<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router;

use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Util\ConfigReaderInterface;

interface RouterInterface extends RouteInterface, ConfigReaderInterface
{

    const STACK_MODE_APPEND = 1;
    const STACK_MODE_PREPEND = 2;

    /**
     * @param RouteInterface $route
     * @param int $mode
     * @return mixed
     */
    public function addRoute(RouteInterface $route, $mode = self::STACK_MODE_APPEND);

    /**
     * @param array $routes
     * @return mixed
     */
    public function addRoutes(array $routes);

    /**
     * @param array $routes
     * @return mixed
     */
    public function setRoutes(array $routes);
}
