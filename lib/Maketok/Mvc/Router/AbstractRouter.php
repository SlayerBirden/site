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
use Maketok\Util\ConfigConsumerInterface;

abstract class AbstractRouter implements RouterInterface, ConfigConsumerInterface
{
    use UtilityHelperTrait;

    /**
     * init
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->clearRoutes();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getRequest()
    {
        //pass, really
        return $this->ioc()->get('request');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
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
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoutes(array $routes)
    {
        return $this->clearRoutes()->addRoutes($routes);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function assemble(array $params = array())
    {
        return '';//pass
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function initConfig()
    {
        $configs = $this->ioc()->get('config_getter')->getConfig(Site::getConfig('routing_provider_path'), 'routes', ENV);
        foreach ($configs as $contents) {
            try {
                $this->parseConfig($contents);
            } catch (RouteException $e) {
                $this->getLogger()->err($e->__toString());
            }
        }
    }

    /**
     * {@inheritdoc}
     * @throws RouteException
     */
    public function parseConfig(array $config)
    {
        $routes = $this->getIfExists('routes', $config, false);
        if (!$routes) {
            return;
        }
        foreach ($routes as $route) {
            $type = $this->getIfExists('type', $route);
            $path = $this->getIfExists('path', $route);
            $resolver = $this->getIfExists('resolver', $route);
            if (is_null($type) || is_null($path) || is_null($resolver)) {
                $this->getLogger()->err(sprintf("Invalid route definition: %s", json_encode($route)));
                continue;
            }
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
        }
    }

    /**
     * @param  string $type
     * @throws RouteException
     * @return string
     */
    protected function getFullyQualifiedName($type)
    {
        if (strpos($type, '\\') === false) {
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
