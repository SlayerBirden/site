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

use Maketok\Util\TokenizedBag;
use Maketok\Util\TokenizedBagPart;

class TokenizedBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testAsArray()
    {
        $bag = new TokenizedBag();
        $bag->addPart(new TokenizedBagPart('var', 'baz'));
        $bag->addPart(new TokenizedBagPart('const', 'bar'));
        $this->assertEquals([
            ['type' => 'var', 'value' => 'baz'],
            ['type' => 'const', 'value' => 'bar']
        ], $bag->asArray());
    }

    /**
     * @test
     */
    public function testCount()
    {
        $bag = new TokenizedBag();
        $this->assertEquals(0, $bag->count());
        $bag->addPart(new TokenizedBagPart('', ''));
        $this->assertEquals(1, $bag->count());
        $bag->addPart(new TokenizedBagPart('', ''));
        $this->assertEquals(2, $bag->count());
    }
}
