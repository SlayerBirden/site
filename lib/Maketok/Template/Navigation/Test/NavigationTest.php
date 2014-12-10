<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Navigation\Test;

use Maketok\Template\Navigation\Link;
use Maketok\Template\Navigation\Navigation;

class NavigationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers Maketok\Template\Navigation\Navigation::addLink
     */
    public function addLink()
    {
        $nav = new Navigation();
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
     * @covers Maketok\Template\Navigation\Navigation::addLink
     * @expectedException \Maketok\Template\Navigation\Exception
     * @expectedExceptionMessage Provided parent is not within current context.
     * @dataProvider wrongParentsProvider
     * @param mixed $parent
     */
    public function addLinkWrongParent($parent)
    {
        $nav = new Navigation();
        $link = new Link('A');
        $nav->addLink($link);
        $nav->addLink(new Link('B'), $parent);
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Navigation::addLink
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid parent provided:
     * @dataProvider invalidArgumentsProvider
     * @param mixed $parent
     */
    public function addLinkInvalidArgument($parent)
    {
        $nav = new Navigation();
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
     * @covers Maketok\Template\Navigation\Navigation::getNavigation
     */
    public function getNavigation()
    {
        $nav = new Navigation();
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
     * @covers Maketok\Template\Navigation\Navigation::addDumper
     */
    public function addDumper()
    {
        $dumper = $this->getMock('Maketok\Template\Navigation\Dumper\DumperInterface');
        $nav = new Navigation();
        $nav->addDumper($dumper);

        $refProp = new \ReflectionProperty(get_class($nav), 'dumpers');
        $refProp->setAccessible(true);
        /** @var \SplStack $stack */
        $stack = $refProp->getValue($nav);

        $this->assertInstanceOf('SplStack', $stack);
        $this->assertEquals($dumper, $stack->pop());
    }
}
