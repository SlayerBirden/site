<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @covers ::getConfig
     * @dataProvider pathProvider
     * @param string $path
     * @param mixed $expected
     */
    public function getConfig($path, $expected)
    {
        $site = new Site();
        $refProp = new \ReflectionProperty(get_class($site), 'config');
        $refProp->setAccessible(true);
        $refProp->setValue($site, [
            'simple' => 'bar',
            'complex' => [
                'null' => null,
                'object' => new \stdClass(),
                'more_complex' => [
                    'test' => []
                ],
                'code' => 'baz'
            ],
        ]);
        $this->assertEquals($expected, $site::getConfig($path));
    }

    /**
     * @return array
     */
    public function pathProvider()
    {
        return [
            ['simple', 'bar'],
            ['simple/', 'bar'],
            ['complex/null', null],
            ['complex/object', new \stdClass()],
            ['complex/more_complex/test', []],
            ['complex/code', 'baz'],
            ['comple', null],
            [null, [
                'simple' => 'bar',
                'complex' => [
                    'null' => null,
                    'object' => new \stdClass(),
                    'more_complex' => [
                        'test' => []
                    ],
                    'code' => 'baz'
                ],
            ]],
        ];
    }
}
