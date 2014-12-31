<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Mvc\Controller;

use Maketok\Http\Request;
use Maketok\Http\Response;
use Maketok\Mvc\Controller\Front;
use Maketok\Mvc\RouteException;
use Maketok\Mvc\Router\Route\Http\Literal;
use Maketok\Mvc\Router\Stack;

class FrontTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function normalFlow()
    {
        $route = $this->getMock('MyRoute', ['index']);
        $route->expects($this->once())
            ->method('index')
            ->willReturn(new Response('', Response::HTTP_OK));
        $router = new Stack();
        $router->addRoute(new Literal('/testroute', [$route, 'index']));

        $front = new Front($router);
        $front->dispatch(Request::create('/testroute'));
    }

    /**
     * @test
     * @expectedException \Maketok\Mvc\RouteException
     */
    public function noRouteFlow()
    {
        $route = $this->getMock('MyRoute', ['index']);
        $route->expects($this->never())->method('index');
        $router = new Stack();
        $router->addRoute(new Literal('/testroute', [$route, 'index']));

        $request = Request::create('/somethingElse');

        $front = new Front($router);
        $front->dispatch($request);
    }

    /**
     * @test
     * @expectedException \ErrorException
     */
    public function errorFlow()
    {
        $route = $this->getMock('MyRoute', ['index']);
        $route->expects($this->once())
            ->method('index')
            ->willThrowException(new \ErrorException('There was an error!'));
        $router = new Stack();
        $router->addRoute(new Literal('/testroute', [$route, 'index']));

        $request = Request::create('/testroute');

        $front = new Front($router);
        $front->dispatch($request);
    }

    /**
     * @test
     */
    public function exceptionHandler()
    {
        $front = new Front(new Stack());
        $request = Request::create('/test');

        $dumper = $this->getMock('ErrorDumper', ['dump']);
        $dumper->expects($this->once())->method('dump')->with($request, Response::HTTP_NOT_FOUND);

        $front->setRequest($request)->addDumper([$dumper, 'dump']);
        $front->exceptionHandler(new RouteException('Not Found'));

        $dumperError = $this->getMock('ErrorDumper', ['dump']);
        $dumperError->expects($this->once())->method('dump')->with($request, Response::HTTP_INTERNAL_SERVER_ERROR);
        $front->addDumper([$dumperError, 'dump']);
        $front->exceptionHandler(new \ErrorException('Horrible Error!'));
    }
}
