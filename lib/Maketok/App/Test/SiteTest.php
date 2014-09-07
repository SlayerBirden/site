<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App\Test;

use Maketok\App\Site;

class SiteTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testGetUrl()
    {
        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $containerMock->expects($this->any())->method('getParameter')->will($this->returnValueMap(array(
            array('base_url', 'http://example.com'),
        )));
        $prop = new \ReflectionProperty('Maketok\App\Site', '_sc');
        $prop->setAccessible(true);
        $prop->setValue($containerMock);
        $this->assertEquals('http://example.com/home/url/', Site::getUrl('home/url'));
        $this->assertEquals('http://example.com/home/url/', Site::getUrl('/home/url'));
        $this->assertEquals('http://example.com/home/url/', Site::getUrl('home/url/'));
        $this->assertEquals('http://example.com/home/url', Site::getUrl('home/url/', array('wts' => 1)));
        $this->assertEquals('http://example.com/home/url/', Site::getUrl('home/url/', array('wts' => 0)));
    }
}
