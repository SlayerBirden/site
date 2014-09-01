<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Test;


use Maketok\Installer\AbstractArrayMerger;

class ArrayMergerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider positiveDataProvider
     */
    public function testConfigElementArrayCompare($a, $b)
    {
        $this->assertTrue(AbstractArrayMerger::configElementArrayCompare($a, $b));
    }

    /**
     * @dataProvider negativeDataProvider
     */
    public function testConfigElementArrayCompareFalse($a, $b)
    {
        $this->assertFalse(AbstractArrayMerger::configElementArrayCompare($a, $b));
    }

    /**
     * @expectedException \Maketok\Installer\MergerException
     * @expectedExceptionMessage Don't know how to compare objects.
     */
    public function testConfigElementArrayCompareWrong()
    {
        AbstractArrayMerger::configElementArrayCompare(new \StdClass(), new \StdClass());
    }

    /**
     * @expectedException \Maketok\Installer\MergerException
     * @expectedExceptionMessage Can not compare elements of different types.
     */
    public function testConfigElementArrayCompareWrong2()
    {
        AbstractArrayMerger::configElementArrayCompare(array(), '');
    }

    public function positiveDataProvider()
    {
        $ar1 = [
            'db' => '1',
            'code' => '2',
        ];
        $ar2 = [
            'code' => '2',
            'db' => '1',
        ];
        $ar3 = [
            'db' => ['c' => '1', 'b' => '2'],
            'code' => '2',
        ];
        $ar4 = [
            'code' => '2',
            'db' => ['b' => '2', 'c' => '1'],
        ];
        return [
            [$ar1, $ar2],
            [$ar3, $ar4],
            ['str1', 'str1'],
        ];
    }

    public function negativeDataProvider()
    {
        $ar1 = [
            'db' => '1',
            'code' => '2',
        ];
        $ar2 = [
            'db' => '2',
            'code' => '2',
        ];
        $ar3 = [
            'db' => ['c' => '1', 'b' => '2'],
            'code' => ['c' => '2'],
        ];
        $ar4 = [
            'code' => ['b' => '2', 'c' => '1'],
            'db' => ['b' => '2', 'c' => '1'],
        ];
        return [
            [$ar1, $ar2],
            [$ar3, $ar4],
            ['str1', 'str2'],
        ];
    }
}
