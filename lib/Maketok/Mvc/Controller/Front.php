<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Controller;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseHttpResponse;
use Maketok\Mvc\FlowException;
use Maketok\Mvc\GenericException;
use Maketok\Mvc\RouteException;
use Maketok\Mvc\Router\Route\Http\Error;
use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Mvc\Router\Route\Success;
use Maketok\Mvc\Router\RouterInterface;
use Maketok\Observer\State;
use Maketok\Util\RequestInterface;
use Maketok\Util\ResponseInterface;

class Front
{
    use UtilityHelperTrait;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var \SplStack
     */
    private $dumpers;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @throws RouteException
     */
    public function dispatch(RequestInterface $request)
    {
        $this->request = $request;
        set_exception_handler([$this, 'exceptionHandler']);
        $this->getDispatcher()->notify('front_before_process', new State(['request' => $request]));
        /** @var Success $success */
        if ($success = $this->router->match($request)) {
            $this->getDispatcher()->notify('match_route_successful', new State(['success' => $success]));
            $this->launch($success);
        } else {
            throw new RouteException("Could not match any route.");
        }
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param Success $success
     * @param bool $silent
     * @param array $parameters
     */
    public function launch(Success $success, $silent = false, $parameters = [])
    {
        $response = $this->launchAction($success->getResolver(), $success->getMatchedRoute(), $parameters);
        if ($response) {
            $this->sendResponse($response, $silent);
        }
    }

    /**
     * @param BaseHttpResponse $response
     * @param bool $silent
     */
    public function sendResponse(BaseHttpResponse $response, $silent = false)
    {
        if (!$silent) {
            $this->getDispatcher()->notify(
                'response_send_before',
                new State(['request' => $this->request, 'response' => $response])
            );
        }
        $response->send();
    }

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->dumpers = new \SplStack();
        $this->dumpers->push(['Maketok\Mvc\Error\Dumper', 'dump']);
    }

    /**
     * Custom exception handler
     * @param  \Exception $e
     * @return void
     */
    public function exceptionHandler(\Exception $e)
    {
        try {
            // a way to regulate flow; do not take any actions
            if ($e instanceof FlowException) {
                return;
            }
            $message = 'Oops! We are really sorry, but there was an error!';
            $dumper = $this->dumpers->pop();
            if ($e instanceof RouteException) {
                // not found
                $code = Response::HTTP_NOT_FOUND;
                $message = "Oops! We couldn't find the page you searched for. Looks like it doesn't exist anymore.";
                $errorRoute = new Error($dumper);
                $this->getDispatcher()->notify('noroute_action', new State(['front' => $this, 'dumper' => $dumper]));
            } else {
                $code = $e->getCode();
                if (!isset(Response::$statusTexts[$code])) {
                    $code = Response::HTTP_INTERNAL_SERVER_ERROR;
                } else {
                    $message = $e->getMessage();
                }
                $errorRoute = new Error($dumper, ['exception' => $e]);
                $this->getLogger()->err($e);
            }
            $this->launch($errorRoute->match($this->request), true, [$code, $message]);
        } catch (\Exception $ex) {
            printf("Exception '%s' thrown within the front controller exception handler in file %s on line %d.\nTrace: %s.\nPrevious exception: %s",
                $ex->getMessage(),
                $ex->getFile(),
                $ex->getLine(),
                $ex->getTraceAsString(),
                $e
            );
        }
    }

    /**
     * @param callable $resolver
     * @param \Maketok\Mvc\Router\Route\RouteInterface $route
     * @param array $params
     * @return ResponseInterface
     * @throws GenericException
     */
    protected function launchAction($resolver, RouteInterface $route, $params = [])
    {
        array_unshift($params, $route->getRequest());
        return call_user_func_array($this->processConfigResolver($resolver), $params);
    }

    /**
     * convert static resolver from config
     * @param callable $definition
     * @return callable
     * @throws GenericException
     */
    public function processConfigResolver($definition)
    {
        // we can't resolve static from config
        if (is_array($definition) && !empty($definition) && is_string(current($definition))) {
            $className = array_shift($definition);
            if (class_exists($className, true)) {
                array_unshift($definition, new $className());
            } else {
                throw new GenericException(sprintf("Can't resolve controller class: %s", $className));
            }
        }

        return $definition;
    }

    /**
     * @param array|callable $dumper
     */
    public function addDumper($dumper)
    {
        $this->dumpers->push($dumper);
    }
}
