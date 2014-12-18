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
use Maketok\Util\TokenizedBagPart;

/**
 * @coversDefaultClass \Maketok\Util\TokenizedBagPart
 */
class TokenizedBagPartTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers ::__construct
     * @covers ::toArray
     */
    public function toArray()
    {
        $part = new TokenizedBagPart('var', 'baz');
        $this->assertEquals(['type' => 'var', 'value' => 'baz'], $part->toArray());
    }
}
