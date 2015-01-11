<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Util;

use Maketok\Util\CallableHash;
use Maketok\Util\Test\MuteStub;

class CallableHashTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider clientProvider
     * @param callable $client1
     * @param callable $client2
     * @param bool $expected
     */
    public function getHash($client1, $client2, $expected)
    {
        $hasher = new CallableHash();
        $this->assertThat($hasher->getHash($client1) == $hasher->getHash($client2), $this->identicalTo($expected));
    }

    /**
     * @return array
     */
    public function clientProvider()
    {
        $muteStub = new MuteStub();
        $cl1 = function ($var) {
            echo $var;
        };
        $cl2 = function ($var) {
            echo $var;
        };
        $cl3 = function ($var) {
            echo $var, PHP_EOL;
        };
        $muteStub2 = new MuteStub();
        $muteStub2->prop = 2;
        return [
            [[$muteStub, 'doSomething'], [$muteStub, 'doSomething'], true],
            [[$muteStub, 'doSomething'], [$muteStub, 'doSomethingElse'], false],
            [[$muteStub, 'doSomething'], [$muteStub2, 'doSomething'], true],
            [$cl1, $cl2, true],
            [$cl1, $cl3, false],
            [[$muteStub, 'doSomething'], $cl1, false],
            [$muteStub, $muteStub2, true],
            ['\Maketok\Util\Test\MuteStub::staticMethod', '\Maketok\Util\Test\MuteStub::staticMethod', true],
        ];
    }
}
