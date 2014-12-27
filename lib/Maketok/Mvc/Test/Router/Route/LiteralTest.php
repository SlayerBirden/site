<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Test\Router\Route;

use Maketok\Http\Request;
use Maketok\Mvc\Router\Route\Http\Literal;

class LiteralTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testMatch()
    {
        $request = Request::create('/super/route');
        $route = new Literal('/super/route', ['bar', 'baz']);

        $success = $route->match($request);
        $this->assertNotFalse($success);
        $this->assertInstanceOf('Maketok\Mvc\Router\Route\Success', $success);
        $this->assertEquals(['bar', 'baz'], $success->getResolver());
        $this->assertSame($route, $success->getMatchedRoute());
        return $route;
    }

    /**
     * @test
     * @depends testMatch
     * @param Literal $route
     */
    public function testAssemble($route)
    {
        $this->assertEquals('/super/route', $route->assemble());
    }

    /**
     * @test
     */
    public function testNotMatch()
    {
        $request = Request::create('/super/route');
        $route = new Literal('/super/r', ['bar', 'baz']);

        $success = $route->match($request);
        $this->assertFalse($success);
    }
}
