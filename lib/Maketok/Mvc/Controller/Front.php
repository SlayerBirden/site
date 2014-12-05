<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
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
use Zend\Stdlib\ErrorHandler;

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
        if ($success = $this->_router->match($state->request)) {
            $this->launch($success);
        } else {
            throw new RouteException("Could not match any route.");
        }
    }

    /**
     * @param Success $success
     */
    public function launch(Success $success)
    {
        $params = $success->getParameters();
        ob_start();
        $response = $this->launchAction($params, $success->getMatchedRoute());
        $content = ob_get_contents();
        // TODO figure out what to do with buffered content
        ob_end_clean();
        $response->send();
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
        try {
            $dumper = Site::getSC()->get('front_controller_error_dumper');
            if ($e instanceof RouteException) {
                // not found
                $errorRoute = new Error(array(
                    'controller' => $dumper,
                    'action' => 'noroute',
                ));
                $this->launch($errorRoute->match(Site::getServiceContainer()->get('request')));
            } elseif ($e instanceof \ErrorException) {
                $errno = $e->getSeverity();
                if ($errno & E_ERROR || $errno & E_RECOVERABLE_ERROR || $errno & E_USER_ERROR) {
                    Site::getServiceContainer()
                        ->get('logger')
                        ->err(sprintf("Front Controller dispatch error exception\n%s", $e->__toString()));
                    $errorRoute = new Error(array(
                        'controller' => $dumper,
                        'action' => 'error',
                        'exception' => $e,
                    ));
                    $this->launch($errorRoute->match(Site::getServiceContainer()->get('request')));
                }

            } else {
                Site::getServiceContainer()
                    ->get('logger')
                    ->emergency(sprintf("Front Controller dispatch unhandled exception\n%s", $e->__toString()));
                $errorRoute = new Error(array(
                    'controller' => $dumper,
                    'action' => 'error',
                    'exception' => $e,
                ));
                $this->launch($errorRoute->match(Site::getServiceContainer()->get('request')));
            }
        } catch (\Exception $ex) {
            printf("Exception '%s' thrown within the front controller exception handler in file %s on line %d. Trace: %s. Previous exception: %s",
                $ex->getMessage(),
                $ex->getFile(),
                $ex->getLine(),
                $ex->getTraceAsString(),
                $e->__toString()
            );
        }
    }

    /**
     * @param array $parameters
     * @param \Maketok\Mvc\Router\Route\RouteInterface $route
     * @throws RouteException
     * @return ResponseInterface
     */
    protected function launchAction(array $parameters, RouteInterface $route)
    {
        if (!isset($parameters['controller']) || !isset($parameters['action'])) {
            throw new RouteException("Missing controller or action for a matched route.");
        }

        if (is_object($parameters['controller'])) {
            $controller = $parameters['controller'];
        } else {
            $controllerClass = (string) $parameters['controller'];
            $controller = new $controllerClass();
         }
        $actionName = $parameters['action'] . 'Action';
        return $controller->$actionName($route->getRequest());
    }
}
