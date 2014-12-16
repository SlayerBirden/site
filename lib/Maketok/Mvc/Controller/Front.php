<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Controller;

use Maketok\App\Helper\UtilityHelperTrait;
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
    use UtilityHelperTrait;


    /** @var  RouteInterface */
    private $router;

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
        if ($success = $this->router->match($state->request)) {
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
        $response = $this->launchAction($success->getResolver(), $success->getMatchedRoute());
        if (!$silent) {
            $this->getDispatcher()->notify('response_send_before', new State());
        }
        if ($response && is_object($response) && ($response instanceof ResponseInterface)) {
            $response->send();
        }
    }

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
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
                $errorRoute = new Error([$dumper, 'norouteAction']);
                $this->getDispatcher()->notify('noroute_action', new State(['front' => $this, 'dumper' => $dumper]));
            } elseif ($e instanceof \ErrorException) {
                $errorRoute = $this->_processError($e, $dumper);
            } else {
                $this->getLogger()->emergency(sprintf("Front Controller dispatch unhandled exception\n%s", $e->__toString()));
                $errorRoute = new Error([$dumper, 'errorAction'], ['exception' => $e]);
            }
            $this->launch($errorRoute->match($this->ioc()->get('request')), true);
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
     * @param \ErrorException $e
     * @param object $dumper controller to handle exceptions
     * @return Error
     */
    protected function _processError(\ErrorException $e, $dumper)
    {
        $errno = $e->getSeverity();
        $message = sprintf("Front Controller dispatch error exception\n%s", $e->__toString());
        if ($errno & E_ERROR || $errno & E_RECOVERABLE_ERROR || $errno & E_USER_ERROR) {
            $this->getLogger()->err($message);
        } elseif ($errno & E_WARNING || $errno & E_USER_WARNING) {
            $this->getLogger()->warn($message);
        } else {
            $this->getLogger()->notice($message);
        }
        return new Error([$dumper, 'errorAction'], ['exception' => $e]);
    }

    /**
     * @param callable $resolver
     * @param \Maketok\Mvc\Router\Route\RouteInterface $route
     * @return ResponseInterface
     */
    protected function launchAction($resolver, RouteInterface $route)
    {
        return call_user_func_array($resolver, array($route->getRequest()));
    }

    /**
     * @param DumperInterface $dumper
     */
    public function addDumper(DumperInterface $dumper)
    {
        $this->dumpers->push($dumper);
    }
}
