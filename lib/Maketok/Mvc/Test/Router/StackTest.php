<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Test\Router;

use Maketok\Mvc\Router\RouterInterface;
use Maketok\Mvc\Router\Stack;
use Maketok\Http\Request;

class StackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function addRoute()
    {
        $router = new Stack();

        $route1 = $this->getMock('\Maketok\Mvc\Router\Route\Http\Literal', [], [], '', false);
        $route1->expects($this->never())->method('match');

        $route2 = $this->getMock('\Maketok\Mvc\Router\Route\Http\Literal', [], [], '', false);
        $route2->expects($this->once())->method('match')->willReturn('bingo');

        $route3 = $this->getMock('\Maketok\Mvc\Router\Route\Http\Literal', [], [], '', false);
        $route3->expects($this->never())->method('match');

        $request = Request::create('/test');
        $this->assertEquals('bingo', $router->addRoute($route1)
            ->addRoute($route2)
            ->addRoute($route3, RouterInterface::STACK_MODE_PREPEND)
            ->match($request)
        );
    }

    /**
     * @test
     */
    public function setRoutes()
    {
        $router = new Stack();

        $route1 = $this->getMock('\Maketok\Mvc\Router\Route\Http\Literal', [], [], '', false);
        $route1->expects($this->never())->method('match');

        $route2 = $this->getMock('\Maketok\Mvc\Router\Route\Http\Literal', [], [], '', false);
        $route2->expects($this->once())->method('match')->willReturn('bingo');

        $request = Request::create('/test');
        $this->assertEquals('bingo', $router->setRoutes([$route1, $route2])->match($request));
    }

    /**
     * @test
     */
    public function clearRoutes()
    {
        $router = new Stack();

        $route1 = $this->getMock('\Maketok\Mvc\Router\Route\Http\Literal', [], [], '', false);
        $route1->expects($this->never())->method('match');
        $router->addRoute($route1); // add 1st route, but it should be removed and never accessed

        $route2 = $this->getMock('\Maketok\Mvc\Router\Route\Http\Literal', [], [], '', false);
        $route2->expects($this->once())->method('match')->willReturn(false);

        $request = Request::create('/test');
        $this->assertFalse($router->setRoutes([$route2])->match($request));
    }
}
