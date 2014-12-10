<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Navigation\Test;

use Maketok\Template\Navigation\Link;

/**
 * @coversDefaultClass \Maketok\Template\Navigation\Link
 */
class LinkTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers ::setOrder
     */
    public function setOrder()
    {
        $link = new Link('test');
        $link->setOrder(10);

        $this->assertEquals(10, $link->getOrder());
    }

    /**
     * @test
     * @covers ::getOrder
     */
    public function getOrder()
    {
        $link = new Link('test');
        $link->setOrder(10);

        $this->assertEquals(10, $link->getOrder());
    }

    /**
     * @test
     * @covers ::setTitle
     */
    public function setTitle()
    {
        $link = new Link('test');
        $link->setTitle('Test');

        $this->assertEquals('Test', $link->getTitle());
    }

    /**
     * @test
     * @covers ::getTitle
     */
    public function getTitle()
    {
        $link = new Link('test');
        $link->setTitle('Test');

        $this->assertEquals('Test', $link->getTitle());
    }

    /**
     * @test
     * @covers ::setCode
     */
    public function setCode()
    {
        $link = new Link('test');
        $link->setCode('test1');

        $this->assertEquals('test1', $link->getCode());
    }

    /**
     * @test
     * @covers ::getCode
     */
    public function getCode()
    {
        $link = new Link('test');

        $this->assertEquals('test', $link->getCode());
    }

    /**
     * @test
     * @covers ::setReference
     */
    public function setReference()
    {
        $link = new Link('test');
        $link->setReference('#1');

        $this->assertEquals('#1', $link->getReference());
    }

    /**
     * @test
     * @covers ::getReference
     */
    public function getReference()
    {
        $link = new Link('test');
        $link->setReference('#1');

        $this->assertEquals('#1', $link->getReference());
    }

    /**
     * @test
     * @covers ::getChildren
     */
    public function getChildren()
    {
        $link = new Link('test');
        $link1 = new Link('test1', '#1', 9);
        $link2 = new Link('test2', '#2', 8);
        $link->addChildren([$link1, $link2]);

        $this->assertEquals([$link2, $link1], $link->getChildren());
    }

    /**
     * @test
     * @covers ::findLink
     */
    public function findLink()
    {
        $link = new Link('test');
        $link1 = new Link('test1');

        $this->assertNull($link->findLink($link1));

        $link->addChild($link1);

        $this->assertEquals($link1, $link->findLink($link1));
    }

    /**
     * @test
     * @covers ::find
     */
    public function find()
    {
        $link = new Link('test');
        $link1 = new Link('test1');

        $this->assertNull($link->find('test1'));

        $link->addChild($link1);

        $this->assertEquals($link1, $link->find('test1'));
    }

    /**
     * @test
     * @covers ::asArray
     */
    public function asArray()
    {
        $link = new Link('test');
        $link1 = new Link('test1', '#1', 9);
        $link2 = new Link('test2', '#2', 8);
        $link->addChildren([$link1, $link2]);

        $this->assertEquals([
            'test' => [
                'href' => null,
                'title' => null,
                'children' => [
                    'test2' => [
                        'href' => '#2',
                        'title' => null,
                        'children' => []
                    ],
                    'test1' => [
                        'href' => '#1',
                        'title' => null,
                        'children' => []
                    ]
                ]
            ]
        ], $link->asArray());
    }
}
