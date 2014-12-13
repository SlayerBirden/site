<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App\Test;

/**
 * @coversDefaultClass \Maketok\App\Helper\UtilityHelperTrait
 */
class UtilityHelperTraitTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers ::getUrl
     * @dataProvider urlProvider
     * @param string $path
     * @param array|null $config
     * @param string $base
     * @param string $expected
     */
    public function testGetUrl($path, $config, $base, $expected)
    {
        $trait = $this->getMockForTrait('Maketok\App\Helper\UtilityHelperTrait');
        $this->assertEquals($expected, $trait->getUrl($path, $config, $base));
    }

    /**
     * @return array
     */
    public function urlProvider()
    {
        return [
            ['home/url', null, 'http://example.com', 'http://example.com/home/url/'],
            ['/home/url', null, 'http://example.com', 'http://example.com/home/url/'],
            ['home/url/', null, 'http://example.com', 'http://example.com/home/url/'],
            ['home/url/', ['wts' => 1], 'http://example.com', 'http://example.com/home/url'],
            ['home/url/', ['wts' => 0], 'http://example.com', 'http://example.com/home/url/']
        ];
    }
}