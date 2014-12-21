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

use Maketok\Util\VersionComparer;

/**
 * @coversDefaultClass \Maketok\Util\VersionComparer
 */
class AbstractManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers       ::natRecursiveCompare
     * @dataProvider versionProvider
     * @param string $v1
     * @param string $v2
     * @param int    $expected
     */
    public function natRecursiveCompare($v1, $v2, $expected)
    {
        $this->assertEquals($expected, VersionComparer::natRecursiveCompare($v1, $v2));
    }

    /**
     * @test
     * @covers ::natRecursiveCompare
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Compared arguments must be strings.
     */
    public function natRecursiveCompareWrongArg()
    {
        VersionComparer::natRecursiveCompare([], '123');
    }

    /**
     * @return array
     */
    public function versionProvider()
    {
        return [
            ['1', '1.0', 0], // version is right-padded
            ['1.0.0.0.0', '1.0', 0],
            ['1.0.0.0.0', '1.0.1', -1],
            ['1.0.0.0.0', '1.0.0.0.0.1', -1],
            ['0.0.0.1', '0.1', -1],
            ['0.0.1', '0.0.0.1.2', 1],
            ['0.0.1', '0.0.0.1.2', 1],
            ['1.0', '0.1.0', 1],
            ['0.1.1', '0.1.0', 1],
            ['0.2', '0.1.9', 1],
            ['1', '0.99999', 1],
            ['0.1', '0.1.0.1', -1], // check to the last version
            ['0.2', '1.0', -1],
            ['22', '22.0.0.1', -1],
            ['0.1', '0.1.0', 0],
            ['0.1.0.1.0', '0.1.0.1', 0],
            ['.0.1', '0.0.1', 0],
            ['v1.0.1', '1.0.1', -1], // implementation doesn't allow usage of literal prefixes like "v"
        ];
    }

    /**
     * @test
     * @covers ::castEqualLength
     * @dataProvider arrayProvider
     * @param array $a
     * @param array $b
     * @param array $expectedA
     * @param array $expectedB
     */
    public function castEqualLength(array $a, array $b, $expectedA, $expectedB)
    {
        VersionComparer::castEqualLength($a, $b);
        $this->assertEquals($expectedA, $a);
        $this->assertEquals($expectedB, $b);
    }

    /**
     * @return array
     */
    public function arrayProvider()
    {
        return [
            [[1,2,3], [2,3], [1,2,3], [2,3,0]],
            [[1,2], [2,3], [1,2], [2,3]],
            [[1,2], [2,3,0], [1,2,0], [2,3,0]],
            [[1,2,0,0,0], [2,3,0], [1,2,0,0,0], [2,3,0,0,0]],
        ];
    }
}
