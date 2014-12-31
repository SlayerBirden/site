<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Mvc\Router\Route;

use Maketok\Http\Request;
use Maketok\Mvc\Router\Route\Http\Error;
use Maketok\Mvc\Router\Route\Http\Literal;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testMatch()
    {
        $request = Request::create('/super/route');
        $route = new Error(['bar', 'baz']);

        $success = $route->match($request);
        $this->assertNotFalse($success);
        $this->assertInstanceOf('Maketok\Mvc\Router\Route\Success', $success);
        $literal = new Literal('/noroute', ['baz', 'noroute']);
        $success->setMatchedRoute($literal);
        $this->assertEquals(['baz', 'noroute'], $success->getResolver());
        $this->assertSame($literal, $success->getMatchedRoute());
        return $route;
    }

    /**
     * @test
     * @depends testMatch
     * @param Error $route
     */
    public function testAssemble($route)
    {
        $this->assertEmpty($route->assemble());
    }
}
