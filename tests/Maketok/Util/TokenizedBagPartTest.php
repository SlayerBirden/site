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

use Maketok\Util\TokenizedBagPart;

class TokenizedBagPartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function toArray()
    {
        $part = new TokenizedBagPart('var', 'baz');
        $this->assertEquals(['type' => 'var', 'value' => 'baz'], $part->toArray());
    }
}
