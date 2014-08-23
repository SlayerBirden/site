<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Mvc\Controller;

use Maketok\App\Site;
use Maketok\Mvc\RouteException;
use Maketok\Mvc\Router\Route\Http\Error;
use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Mvc\Router\Route\Success;
use Maketok\Mvc\Router\RouterInterface;
use Maketok\Observer\StateInterface;
use Maketok\Util\ResponseInterface;

class Front
{


    /** @var  RouteInterface */
    private $_router;

    /**
     * @param StateInterface $state
     * @throws RouteException
     */
    public function dispatch(StateInterface $state)
    {
        set_exception_handler(array($this, 'exceptionHandler'));
        /** @var Success $success */
        if ($success = $this->_getRouter()->match($state->request)) {
            $this->launch($success);
        }
        throw new RouteException("Could not match any route.");
    }

    /**
     * @param Success $success
     */
    public function launch(Success $success)
    {
        $params = $success->getParameters();
        ob_start();
        $response = $this->_launchAction($params, $success->getMatchedRoute());
        $content = ob_get_contents();
        // TODO figure out what to do with buffered content
        ob_end_clean();
        $response->send();
        restore_exception_handler();
        exit;
    }

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->_router = $router;
    }

    /**
     * Custom exception handler
     * @param \Exception $e
     * @return \Maketok\Mvc\Router\Route\Success
     */
    public function exceptionHandler(\Exception $e)
    {
        $dumperClass = 'Maketok\Mvc\Router\Route\Http\Error\Dumper';
        if (Site::getServiceContainer()->hasParameter('front_controller_error_dumper')) {
            $dumperClass = Site::getServiceContainer()->getParameter('front_controller_error_dumper');
        }
        if ($e instanceof RouteException) {
            // not found
            $errorRoute = new Error(array(
                'controller' => $dumperClass,
                'action' => 'noroute',
            ));
            $this->launch($errorRoute->match(Site::getRequest()));
        } else {
            Site::getServiceContainer()
                ->get('logger')
                ->emergency(sprintf("Front Controller dispatch unhandled exception\n%s", $e->__toString()));
            $errorRoute = new Error(array(
                'controller' => $dumperClass,
                'action' => 'error',
            ));
            $this->launch($errorRoute->match(Site::getRequest()));
        }
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
     * @throws RouteException
     * @return ResponseInterface
     */
    protected function _launchAction(array $parameters, RouteInterface $route)
    {
        if (!isset($parameters['controller']) || !isset($parameters['action'])) {
            throw new RouteException("Missing controller or action for a matched route.");
        }

        $controller = new $parameters['controller'];
        $actionName = $parameters['action'] . 'Action';
        return $controller->$actionName($route->getRequest());
    }
}
