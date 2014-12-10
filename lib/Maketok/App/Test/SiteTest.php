<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App\Test;

use Maketok\App\Site;

/**
 * @coversDefaultClass \Maketok\App\Site
 */
class SiteTest extends \PHPUnit_Framework_TestCase
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
        $this->assertEquals($expected, Site::getUrl($path, $config, $base));
    }

    /**
     * @test
     * @covers ::getSCFilePrefix
     * @param string|string[] $code
     * @param string $expected
     * @dataProvider prefixProvider
     */
    public function getContainerFileName($code, $expected)
    {
        $this->assertEquals($expected, Site::getSCFilePrefix($code));
    }

    /**
     * @return array
     */
    public function prefixProvider()
    {
        return [
            ['env', 'env.'],
            [['local', 'env'], 'local.env.']
        ];
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
