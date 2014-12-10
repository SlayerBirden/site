<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Mvc\RouteException;
use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Util\RequestInterface;

class Stack implements RouterInterface
{
    use UtilityHelperTrait;


    /** @var \SplStack  */
    protected $_routes;

    public function __construct()
    {
        $this->_routes = new \SplStack();
    }

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
    public function addRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->_routes->push($route);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setRoutes(array $routes)
    {
        // we need to clear stack first
        while($this->_routes->count() > 0) {
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
    public function assemble(array $params = array())
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return array();
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
                $parameters = $this->getIfExists('parameters', $route);
                if (is_null($type) || is_null($path) || is_null($parameters)) {
                    $this->getLogger()->err(sprintf("Invalid route definition: %s", json_encode($route)));
                    continue;
                }
                try {
                    $name = $this->getFullyQualifiedName($type);
                    /** @var RouterInterface $routeObj */
                    $routeObj = new $name($path,
                        $parameters,
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
