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

use Maketok\App\Helper\ContainerTrait;
use Maketok\Navigation\Link;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    use ContainerTrait;
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
                        'children' => [],
                        'code' => 'test2',
                        'is_active' => false,
                        'full_reference' => 'http://mysite.com/',
                    ],
                    'test1' => [
                        'href' => '#1',
                        'title' => null,
                        'children' => [],
                        'code' => 'test1',
                        'is_active' => false,
                        'full_reference' => 'http://mysite.com/',
                    ]
                ],
                'code' => 'test',
                'is_active' => true,
                'full_reference' => '',
            ]
        ], $link->asArray());
    }

    /**
     * @test
     * @dataProvider activeProvider
     * @param string $href
     * @param string $url
     * @param bool $expected
     */
    public function isActive($href, $url, $expected)
    {
        $mocked = $this->getMock('Maketok\Navigation\Link', ['getCurrentUrl', 'getUrl'], ['test1', $href, 9]);
        $mocked->expects($this->any())->method('getCurrentUrl')->willReturn($url);
        $mocked->expects($this->any())->method('getUrl')->willReturnMap([
            ['test', [], null, 'http://site.com/test/'],
            ['test/', [], null, 'http://site.com/test/'],
            ['test/new', [], null, 'http://site.com/test/new/'],
        ]);
        /** @var Link $mocked */
        $this->assertEquals($expected, $mocked->isActive());
    }

    /**
     * @return array
     */
    public function activeProvider()
    {
        return [
            ['test', 'http://site.com/test/', true],
            ['test/', 'http://site.com/test/new', true],
            ['test/new', 'http://site.com/test/n/', false],
            ['http://site.com', 'http://site.com/test/', false],
            ['http://site.com/test', 'http://site.com/test/', true],
            ['http://site.com/test/', 'http://site.com/test/new', true],
        ];
    }

    /**
     * @test
     * @dataProvider refProvider
     * @param string $href
     * @param string $expected
     */
    public function getFullReference($href, $expected)
    {
        $link1 = new Link('test1', $href, 9);
        $this->assertEquals($expected, $link1->getFullReference());
    }

    /**
     * @return array
     */
    public function refProvider()
    {
        $baseUrl = $this->ioc()->getParameter('base_url');
        return [
            ['#1', $baseUrl . '/'],
            ['test', $baseUrl . '/test/'],
            ['http://site.com', 'http://site.com'],
        ];
    }
}
