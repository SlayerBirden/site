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
use Maketok\Mvc\Router\Route\Http\Parameterized;
use Maketok\Util\ExpressionParser;
use Maketok\Util\Tokenizer;

class ParameterizedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testMatch()
    {
        $parser = new ExpressionParser(
            '/super/route/view/p/{p}',
            new Tokenizer('/super/route/view/p/{p}'),
            ['p' => 1],
            ['p' => '^\d+$']
        );
        $request = Request::create('/super/route/view/p/3');
        $route = new Parameterized(
            '/super/route/view/p/{p}',
            ['bar', 'baz'],
            ['p' => 1],
            ['p' => '^\d+$'],
            $parser
        );

        $success = $route->match($request);
        $this->assertNotFalse($success);
        $this->assertInstanceOf('Maketok\Mvc\Router\Route\Success', $success);
        return $route;
    }

    /**
     * @test
     * @depends testMatch
     * @param Parameterized $route
     */
    public function testAssemble($route)
    {
        $this->assertEquals('/super/route/view/p/5', $route->assemble(['p' => 5]));
    }

    /**
     * @test
     */
    public function testNotMatch()
    {
        $request = Request::create('/super/route/view/p/3/order/bestseller');
        $route = new Parameterized('/super/route/view/p/{p}', ['bar', 'baz'], ['p' => 1], ['p' => '^\d+$']);

        $success = $route->match($request);
        $this->assertFalse($success);
    }
}
