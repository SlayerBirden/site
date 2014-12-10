<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Test;

/**
 * Class ArrayValueTraitTest
 * @package Maketok\Util\Test
 * @coversDefaultClass \Maketok\Util\ArrayValueTrait
 */
class ArrayValueTraitTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers ::getIfExists
     * @dataProvider provider
     * @param string|string[] $key
     * @param array $data
     * @param mixed $default
     * @param mixed $expected
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
