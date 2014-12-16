<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router;

use Maketok\App\Helper\UtilityHelperTrait;
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
     * @param array $routes
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
    public function source($path)
    {
        $contents = $this->parseYaml($path);
        if ($routes = $this->getIfExists('routes', $contents, false)) {
            foreach ($routes as $route) {
                $type = $this->getIfExists('type', $route);
                $path = $this->getIfExists('path', $route);
                $resolver = $this->getIfExists('resolver', $route);
                $resolver = $this->processConfigResolver($resolver);
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

    /**
     * convert static resolver from config
     * @param $definition
     * @return callable
     */
    public function processConfigResolver($definition)
    {
        // we can't resolve static from config
        if (is_array($definition) && !empty($definition) && is_string(current($definition))) {
            $className = array_shift($definition);
            if (class_exists($className, true)) {
                array_unshift($definition, new $className());
            } else {
                array_unshift($definition, $className);
            }
        }
        return $definition;
    }

    /**
     * @param string $type
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