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

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\App\Site;
use Maketok\Mvc\RouteException;
use Maketok\Util\RequestInterface;

abstract class AbstractRouter implements RouterInterface
{
    use UtilityHelperTrait;

    /**
     * init
     */
    public function __construct()
    {
        $this->clearRoutes();
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        //pass really
        return $this->ioc()->get('request');
    }

    /**
     * @return callable
     */
    public function getResolver()
    {
        return [];//pass
    }

    /**
     * {@inheritdoc}
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }
    }

    /**
     * @param  array $routes
     * @return mixed
     */
    public function setRoutes(array $routes)
    {
        $this->clearRoutes()->addRoutes($routes);
    }

    /**
     * {@inheritdoc}
     */
    public function assemble(array $params = array())
    {
        return '';//pass
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfig()
    {
        $configs = $this->ioc()->get('config_getter')->getConfig(Site::getConfig('routing_provider_path'), 'routes', ENV);
        foreach ($configs as $contents) {
            if ($routes = $this->getIfExists('routes', $contents, false)) {
                foreach ($routes as $route) {
                    $type = $this->getIfExists('type', $route);
                    $path = $this->getIfExists('path', $route);
                    $resolver = $this->getIfExists('resolver', $route);
                    if (is_null($type) || is_null($path) || is_null($resolver)) {
                        $this->getLogger()->err(sprintf("Invalid route definition: %s", json_encode($route)));
                        continue;
                    }
                    try {
                        $name = $this->getFullyQualifiedName($type);
                        /** @var RouterInterface $routeObj */
                        $routeObj = new $name(
                            $path,
                            $resolver,
                            $this->getIfExists('defaults', $route, []),
                            $this->getIfExists('restrictions', $route, []),
                            $this->getIfExists('parser', $route)
                        );
                        $this->addRoute($routeObj);
                    } catch (RouteException $e) {
                        $this->getLogger()->err($e->__toString());
                    }
                }
            }
        }
    }

    /**
     * @param  string         $type
     * @throws RouteException
     * @return string
     */
    protected function getFullyQualifiedName($type)
    {
        if (!strpos($type, '\\')) {
            $fullQualifiedRouteName = '\Maketok\Mvc\Router\Route\Http\\' . ucfirst($type);
        } else {
            $fullQualifiedRouteName = $type;
        }
        if (!class_exists($fullQualifiedRouteName)) {
            throw new RouteException(sprintf("Invalid route type: %s", $type));
        }

        return $fullQualifiedRouteName;
    }
}
