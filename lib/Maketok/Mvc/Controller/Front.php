<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Mvc\Controller;

use Maketok\App\Site;
use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Observer\StateInterface;
use Maketok\Util\ResponseInterface;

class Front
{


    /** @var  RouteInterface */
    private $_router;

    /**
     * @param StateInterface $state
     * @throws \Exception
     */
    public function dispatch(StateInterface $state)
    {
        if ($success = $this->_getRouter()->match($state->request)) {
            $params = $success->getParameters();
            ob_start();
            $response = $this->_launchAction($params, $success->getMatchedRoute());
            $content = ob_get_contents();
            // TODO figure out what to do with buffered content
            ob_end_clean();
            $response->prepare($state->request);
            $response->send();
            exit;
        }
        throw new \Exception("Could not match any route.");
    }

    public function __construct()
    {
        // request for a router
        $this->_router = Site::getCurrentRouter();
    }

    /**
     * @return RouteInterface
     */
    protected function _getRouter()
    {
        return $this->_router;
    }

    /**
     * @param array $parameters
     * @param \Maketok\Mvc\Router\Route\RouteInterface $route
     * @throws \Exception
     * @return ResponseInterface
     */
    protected function _launchAction(array $parameters, RouteInterface $route)
    {
        if (!isset($parameters['controller']) || !isset($parameters['action'])) {
            throw new \Exception("Missing controller or action for a matched route.");
        }

        $controller = new $parameters['controller'];
        $actionName = $parameters['action'] . 'Action';
        return $controller->$actionName($route->getRequest());
    }
}