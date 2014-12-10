<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Controller;

use Maketok\App\Site;
use Maketok\Mvc\Error\Dumper;
use Maketok\Mvc\RouteException;
use Maketok\Mvc\Router\Route\Http\Error;
use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Mvc\Router\Route\Success;
use Maketok\Mvc\Router\RouterInterface;
use Maketok\Observer\State;
use Maketok\Observer\StateInterface;
use Maketok\Util\ResponseInterface;
use Maketok\Mvc\Error\DumperInterface;

class Front
{


    /** @var  RouteInterface */
    private $_router;

    /** @var \SplStack */
    private $dumpers;

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
     * @param bool $silent
     * @throws RouteException
     */
    public function launch(Success $success, $silent = false)
    {
        $params = $success->getParameters();
        ob_start();
        $response = $this->launchAction($params, $success->getMatchedRoute());
        $content = ob_get_contents();
        // TODO figure out what to do with buffered content
        ob_end_clean();
        if (!$silent) {
            Site::getSC()->get('subject_manager')->notify('response_send_before', new State());
        }
        $response->send();
    }

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->_router = $router;
        $this->dumpers = new \SplStack();
        $this->dumpers->push(new Dumper());
    }

    /**
     * Custom exception handler
     * @param \Exception $e
     * @return void
     */
    public function exceptionHandler(\Exception $e)
    {
        try {
            $dumper = $this->dumpers->pop();
            if ($e instanceof RouteException) {
                // not found
                $errorRoute = new Error(array(
                    'controller' => $dumper,
                    'action' => 'noroute',
                ));
                $this->launch($errorRoute->match(Site::getServiceContainer()->get('request')), true);
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
                    $this->launch($errorRoute->match(Site::getServiceContainer()->get('request')), true);
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
                $this->launch($errorRoute->match(Site::getServiceContainer()->get('request')), true);
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
        $actionName = $parameters['action'];
        if (!method_exists($controller, $actionName)) {
            $actionName .= 'Action';
        }
        if (!method_exists($controller, $actionName)) {
            throw new RouteException("Non existing action name.");
        }
        return $controller->$actionName($route->getRequest());
    }

    /**
     * @param DumperInterface $dumper
     */
    public function addDumper(DumperInterface $dumper)
    {
        $this->dumpers->push($dumper);
    }
}
