<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\App;

class UtilityHelperTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider urlProvider
     * @param string     $path
     * @param array|null $config
     * @param string     $base
     * @param string     $expected
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
            ['home/url', [], 'http://example.com', 'http://example.com/home/url/'],
            ['/home/url', [], 'http://example.com', 'http://example.com/home/url/'],
            ['home/url/', [], 'http://example.com', 'http://example.com/home/url/'],
            ['home/url/', ['wts' => 1], 'http://example.com', 'http://example.com/home/url'],
            ['home/url/', ['wts' => 0], 'http://example.com', 'http://example.com/home/url/'],
            ['home/url/', [], 'http://example.com/admin', 'http://example.com/admin/home/url/'],
            ['home/url/', ['clear_path' => 1], 'http://example.com/admin', 'http://example.com/home/url/'],
        ];
    }
}
