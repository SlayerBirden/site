<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util\Test;

class ArrayValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider provider
     * @param string|string[] $key
     * @param array           $data
     * @param mixed           $default
     * @param mixed           $expected
     */
    public function getIfExists($key, $data, $default, $expected)
    {
        /** @var \Maketok\Util\ArrayValueTrait $trait */
        $trait = $this->getMockForTrait('Maketok\Util\ArrayValueTrait');

        $this->assertEquals($expected, $trait->getIfExists($key, $data, $default));
    }

    /**
     * @return array
     */
    public function provider()
    {
        return [
            ['k', ['k' => 1], null, 1],
            ['k', ['a' => 1], 2, 2],
            ['k', ['a' => 1], null, null],
            [['a', 'l'], ['a' => ['l' => 1]], null, 1],
            [['a', 'b'], ['a' => ['l' => 1]], null, null],
            [['l', 'a'], ['a' => ['l' => 1]], 2, 2],
            [['l', 'a'], ['a' => ['l' => 1]], [], []],
            [['a', 'l', 'c'], ['a' => ['l' => ['c' => []]]], null, []],
        ];
    }
}
