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

use Maketok\Util\Tokenizer;

/**
 * @coversDefaultClass \Maketok\Util\Tokenizer
 */
class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::__construct
     * @covers ::getCurrentMode
     * @covers ::flowControl
     * @covers ::changeMode
     * @covers ::flushCurrentStorage
     */
    public function flowControl()
    {
        $tokenizer = new Tokenizer('');
        $this->assertNull($tokenizer->getCurrentMode());
        $tokenizer->flowControl($tokenizer->variableCharBegin);
        $this->assertEquals(Tokenizer::MODE_VAR, $tokenizer->getCurrentMode());
        $tokenizer->flowControl($tokenizer->variableCharEnd);
        $this->assertEquals(Tokenizer::MODE_CONST, $tokenizer->getCurrentMode());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::flowControl
     * @covers ::changeMode
     * @expectedException \Maketok\Util\Exception\TokenizerException
     * @expectedExceptionMessage Does not allow nested types.
     */
    public function changeModeNestedTypeError()
    {
        $tokenizer = new Tokenizer('');
        $tokenizer->flowControl($tokenizer->variableCharBegin)
            ->flowControl($tokenizer->variableCharBegin);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getCurrentVar
     * @covers ::getCurrentConst
     * @covers ::assign
     * @covers ::flowControl
     * @covers ::changeMode
     * @covers ::flushCurrentStorage
     */
    public function assign()
    {
        $tokenizer = new Tokenizer('');
        $tokenizer->flowControl($tokenizer->variableCharBegin)->assign('a');
        $this->assertEquals('a', $tokenizer->getCurrentVar());
        $tokenizer->flowControl($tokenizer->variableCharEnd)->assign('b');
        $this->assertEquals('b', $tokenizer->getCurrentConst());
        $tokenizer->flowControl($tokenizer->variableCharBegin);
        $this->assertEmpty($tokenizer->getCurrentVar());
        $this->assertEmpty($tokenizer->getCurrentConst());
    }

    /**
     * @test
     * @covers ::tokenize
     * @covers ::flowControl
     * @covers ::changeMode
     * @covers ::flushCurrentStorage
     * @covers ::assign
     * @dataProvider expressionProvider
     * @param string $expression
     * @param array  $expected
     */
    public function tokenize($expression, $expected)
    {
        $tokenizer = new Tokenizer($expression);
        $this->assertEquals($expected, $tokenizer->tokenize()->asArray());
    }

    /**
     * @return array
     */
    public function expressionProvider()
    {
        return [
            ['string', [
                ['type' => 'const', 'value' => 'string']
            ]],
            ['string/{a}', [
                ['type' => 'const', 'value' => 'string/'],
                ['type' => 'var', 'value' => 'a']
            ]],
            ['string/{a}bar{baz}', [
                ['type' => 'const', 'value' => 'string/'],
                ['type' => 'var', 'value' => 'a'],
                ['type' => 'const', 'value' => 'bar'],
                ['type' => 'var', 'value' => 'baz']
            ]],
            ['{foo}', [
                ['type' => 'var', 'value' => 'foo']
            ]],
            ['blog/article/{id}/cover', [
                ['type' => 'const', 'value' => 'blog/article/'],
                ['type' => 'var', 'value' => 'id'],
                ['type' => 'const', 'value' => '/cover'],
            ]],
        ];
    }
}
