<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
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
