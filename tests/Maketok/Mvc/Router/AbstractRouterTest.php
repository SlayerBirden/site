<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Mvc\Router;

class AbstractRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function parseConfig()
    {
        $router = $this->getMockForAbstractClass('\Maketok\Mvc\Router\AbstractRouter');
        $router->expects($this->atLeast(3))->method('addRoute');

        $router->parseConfig([
            'routes' => [
                [
                    'type' => 'literal',
                    'path' => '/test/',
                    'resolver' => ['ControllerMock', 'index']
                ],
                [
                    'type' => '\Maketok\Mvc\Router\Route\Http\Error',
                    'path' => '/test/view',
                    'resolver' => ['ControllerMock', 'view']
                ],
                [
                    'type' => 'parameterized',
                    'path' => '/test/list/{p}',
                    'resolver' => ['ControllerMock', 'list'],
                    'defaults' => ['p' => 1],
                    'restrictions' => ['p' => '^\d+']
                ]
            ]
        ]);
    }

    /**
     * @test
     * @expectedException \Maketok\Mvc\RouteException
     * @expectedExceptionMessage Invalid route type:
     */
    public function parseConfigInvalidRoute()
    {
        $router = $this->getMockForAbstractClass('\Maketok\Mvc\Router\AbstractRouter');
        $router->expects($this->never())->method('addRoute');

        $router->parseConfig([
            'routes' => [
                [
                    'type' => '\Your_Not_Existing_Route',
                    'path' => '/test/',
                    'resolver' => ['bar', 'baz']
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function parseConfigNoConfig()
    {
        $router = $this->getMockForAbstractClass('\Maketok\Mvc\Router\AbstractRouter');
        $router->expects($this->never())->method('addRoute');

        $router->parseConfig([
            'test' => [
                [
                    'type' => 'literal',
                    'path' => '/test/',
                    'resolver' => ['bar', 'baz']
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function parseConfigOneRouteConfigMissing()
    {
        $router = $this->getMockForAbstractClass('\Maketok\Mvc\Router\AbstractRouter');
        $router->expects($this->once())->method('addRoute');

        $router->parseConfig([
            'routes' => [
                [
                    'type' => 'literal',
                    'path' => '/test/',
                    'resolver' => ['bar', 'baz']
                ],
                [
                    'type' => 'literal',
                    'path' => '/test/',
                ]
            ]
        ]);
    }
}
