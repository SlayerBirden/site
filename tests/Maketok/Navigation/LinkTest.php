<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Navigation;

use Maketok\Navigation\Link;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function setOrder()
    {
        $link = new Link('test');
        $link->setOrder(10);

        $this->assertEquals(10, $link->getOrder());
    }

    /**
     * @test
     */
    public function getOrder()
    {
        $link = new Link('test');
        $link->setOrder(10);

        $this->assertEquals(10, $link->getOrder());
    }

    /**
     * @test
     */
    public function setTitle()
    {
        $link = new Link('test');
        $link->setTitle('Test');

        $this->assertEquals('Test', $link->getTitle());
    }

    /**
     * @test
     */
    public function getTitle()
    {
        $link = new Link('test');
        $link->setTitle('Test');

        $this->assertEquals('Test', $link->getTitle());
    }

    /**
     * @test
     */
    public function setCode()
    {
        $link = new Link('test');
        $link->setCode('test1');

        $this->assertEquals('test1', $link->getCode());
    }

    /**
     * @test
     */
    public function getCode()
    {
        $link = new Link('test');

        $this->assertEquals('test', $link->getCode());
    }

    /**
     * @test
     */
    public function setReference()
    {
        $link = new Link('test');
        $link->setReference('#1');

        $this->assertEquals('#1', $link->getReference());
    }

    /**
     * @test
     */
    public function getReference()
    {
        $link = new Link('test');
        $link->setReference('#1');

        $this->assertEquals('#1', $link->getReference());
    }

    /**
     * @test
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
