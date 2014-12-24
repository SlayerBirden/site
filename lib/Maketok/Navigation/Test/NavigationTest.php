<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Navigation\Test;

use Maketok\Navigation\Link;
use Maketok\Navigation\Navigation;

/**
 * @coversDefaultClass \Maketok\Navigation\Navigation
 */
class NavigationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::addLink
     */
    public function addLink()
    {
        $nav = new Navigation('test');
        $link = new Link('A');
        $nav->addLink($link);
        $nav->addLink(new Link('B'), 'A');
        $nav->addLink(new Link('C'), $link);
        $nav->addLink(new Link('D'), 'C');

        $this->assertEquals([
            'A' => [
                'href' => null,
                'title' => null,
                'children' => [
                    'B' => [
                        'href' => null,
                        'title' => null,
                        'children' => []
                    ],
                    'C' => [
                        'href' => null,
                        'title' => null,
                        'children' => [
                            'D' => [
                                'href' => null,
                                'title' => null,
                                'children' => []
                            ]
                        ]
                    ]
                ]
            ]
        ], $nav->getNavigation());
    }

    /**
     * @test
     * @covers ::addLink
     * @expectedException \Maketok\Navigation\Exception
     * @expectedExceptionMessage Provided parent is not within current context.
     * @dataProvider wrongParentsProvider
     * @param mixed $parent
     */
    public function addLinkWrongParent($parent)
    {
        $nav = new Navigation('test');
        $link = new Link('A');
        $nav->addLink($link);
        $nav->addLink(new Link('B'), $parent);
    }

    /**
     * @test
     * @covers ::addLink
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid parent provided:
     * @dataProvider invalidArgumentsProvider
     * @param mixed $parent
     */
    public function addLinkInvalidArgument($parent)
    {
        $nav = new Navigation('test');
        $nav->addLink(new Link('B'), $parent);
    }

    /**
     * @return array
     */
    public function wrongParentsProvider()
    {
        return [
            ['a'],
            [new Link('D')]
        ];
    }

    /**
     * @return array
     */
    public function invalidArgumentsProvider()
    {
        return [
            [1],
            [['A']],
            [1.0],
        ];
    }

    /**
     * @test
     * @covers ::getNavigation
     */
    public function getNavigation()
    {
        $nav = new Navigation('test');
        $nav->addLink(new Link('A'));
        $this->assertEquals([
            'A' => [
                'href' => null,
                'title' => null,
                'children' => []
            ]
        ], $nav->getNavigation());
    }

    /**
     * @test
     * @covers ::addDumper
     */
    public function addDumper()
    {
        $dumper = $this->getMock('Maketok\Navigation\Dumper\DumperInterface');
        $nav = new Navigation('test');
        $nav->addDumper($dumper);

        $refProp = new \ReflectionProperty(get_class($nav), 'dumpers');
        $refProp->setAccessible(true);
        /** @var \SplStack $stack */
        $stack = $refProp->getValue($nav);

        $this->assertInstanceOf('SplStack', $stack);
        $this->assertEquals($dumper, $stack->pop());
    }

    /**
     * @test
     * @covers ::parseConfig
     * @covers Maketok\Navigation\Node::addChild
     * @covers ::getNavigation
     */
    public function parseConfig()
    {
        $config = [
            'A' => [
                'href' => '/linkA',
                'title' => 'Link A',
                'children' => [
                    'B' => [
                        'href' => '/linkB',
                        'title' => 'Link B',
                        'children' => []
                    ]
                ]
            ]
        ];
        $nav = new Navigation('test');
        $nav->parseConfig($config);
        $this->assertEquals($config, $nav->getNavigation());
    }

    /**
     * @test
     * @covers ::parseConfig
     * @expectedException \Maketok\Navigation\Exception
     * @expectedExceptionMessage Invalid link type given:
     */
    public function parseConfigInvalidLink()
    {
        $config = [
            'A' => 'test'
        ];
        $nav = new Navigation('test');
        $nav->parseConfig($config);
    }
}
