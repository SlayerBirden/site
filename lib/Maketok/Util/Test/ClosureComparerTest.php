<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Test;
use Maketok\Util\ClosureComparer;

/**
 * @coversDefaultClass \Maketok\Util\ClosureComparer
 */
class ClosureComparerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ClosureComparer
     */
    private $comparer;

    public function setUp()
    {
        $this->comparer = new ClosureComparer();
    }

    /**
     * @test
     * @covers ::compare
     * @covers ::isClosure
     * @covers ::parseClosure
     * @covers ::filterBody
     * @dataProvider closureProvider
     * @param mixed $c1
     * @param mixed $c2
     * @param int $expected
     */
    public function compare($c1, $c2, $expected)
    {
        $this->assertEquals($expected, $this->comparer->compare($c1, $c2));
    }

    /**
     * @return array
     */
    public function closureProvider()
    {
        $c1 = function() {echo 'Closure 1';};
        $c2 = function() {
            echo 'Closure 2';
        };
        $c3 = function() {
            echo 'Closure 1'
            ;
        };
        $c4 = function($var) {
            echo $var;
        };
        $e = 1;
        $c5 = function($var) use ($e) {
            echo $var;
        };
        $c6 = function() {
            'echo $var';
        };
        $c7 = function($var) {
            'echo $var';
        };
        return [
            [$c1, $c2, 1],
            [$c1, $c3, 0],
            [$c4, $c5, 0],
            [$c4, $c6, 1],
            [$c4, $c7, 1],
            [$c6, $c7, 1]
        ];
    }

    /**
     * @test
     * @covers ::isClosure
     * @dataProvider variableProvider
     * @param mixed $var
     * @param bool $expected
     */
    public function isClosure($var, $expected)
    {
        $this->assertEquals($expected, $this->comparer->isClosure($var));
    }

    /**
     * @return array
     */
    public function variableProvider()
    {
        return [
            ['123', false],
            [new \stdClass(), false],
            [[], false],
            [function ($var) {echo $var;}, true],
        ];
    }

    /**
     * @test
     * @covers ::parseClosure
     * @covers ::filterBody
     * @dataProvider textProvider
     * @param string $contents
     * @param string $expected
     */
    public function parseClosure($contents, $expected)
    {
        $this->assertEquals($expected, $this->comparer->parseClosure($contents));
    }


    /**
     * @return array
     */
    public function textProvider()
    {
        return [
            ['function() {...}', '...'],
            ['function() {
                bla
                ;
            }', 'bla;'],
        ];
    }
}
