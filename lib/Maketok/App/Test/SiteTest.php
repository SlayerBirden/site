<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App\Test;

use Maketok\App\Site;

class SiteTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers Maketok\App\Site::getUrl
     */
    public function testGetUrl()
    {
        $bUrl = 'http://example.com';
        $this->assertEquals('http://example.com/home/url/', Site::getUrl('home/url', null, $bUrl));
        $this->assertEquals('http://example.com/home/url/', Site::getUrl('/home/url', null, $bUrl));
        $this->assertEquals('http://example.com/home/url/', Site::getUrl('home/url/', null, $bUrl));
        $this->assertEquals('http://example.com/home/url', Site::getUrl('home/url/', array('wts' => 1), $bUrl));
        $this->assertEquals('http://example.com/home/url/', Site::getUrl('home/url/', array('wts' => 0), $bUrl));
    }

    /**
     * @test
     * @covers Maketok\App\Site::getSCFilePrefix
     * @param string|string[] $code
     * @param string $expected
     * @dataProvider prefixProvider
     */
    public function getSCFilePrefix($code, $expected)
    {
        $this->assertEquals($expected, Site::getSCFilePrefix($code));
    }

    public function prefixProvider()
    {
        return [
            ['env', 'env.'],
            [['local', 'env'], 'local.env.']
        ];
    }
}
