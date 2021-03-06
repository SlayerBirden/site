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

use Maketok\Util\ExpressionParser;
use Maketok\Util\Tokenizer;

class ExpressionParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @param string $exprString
     * @param array  $parameters
     * @param string $expected
     * @dataProvider expressionEvalProvider
     */
    public function testEvaluate($exprString, $parameters, $expected)
    {
        $expr = new ExpressionParser($exprString, new Tokenizer($exprString), $parameters);
        $this->assertEquals($expected, $expr->evaluate());
    }

    /**
     * @return array
     */
    public function expressionEvalProvider()
    {
        return [
            ['/blog/article/{article_id}', ['article_id' => 3], '/blog/article/3']
        ];
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage One of the parameters (article_id) failed to satisfy requirements.
     */
    public function testEvaluateFail()
    {
        $exprString = '/blog/article/{article_id}';
        $expr = new ExpressionParser(
            $exprString,
            new Tokenizer($exprString),
            ['article_id' => 'sdf'],
            ['article_id' => '\d+']);
        $expr->evaluate();
    }

    /**
     * @test
     * @param string $exprString
     * @param array  $restrictions
     * @param string $newString
     * @param mixed  $expected
     * @dataProvider expressionProvider
     */
    public function testParse($exprString, $newString, $restrictions, $expected)
    {
        $expr = new ExpressionParser(
            $exprString,
            new Tokenizer($exprString),
            [],
            $restrictions
        );
        $this->assertEquals($expected, $expr->parse($newString));
    }

    /**
     * @return array
     */
    public function expressionProvider()
    {
        return [
            ['/blog/article/{article_id}', '/blog/article/123', [], ['article_id' => '123']],
            ['/blog/article/{article_id}/cat', '/blog/article/123/cat', [], ['article_id' => '123']],
            ['/blog/article/{article_id}/dog', '/blog/article/123/cat', [], false],
            ['/blog/article', '/blog/article', [], []],
            ['/blog/article', '/blog/tv', [], false],
            ['/blog/{bla}', 'bla/blog/', [], false],
            ['/blog/article/{article_id}', '/blog/article/', [], false],
            ['/blog/article/{article_id}', '/blog/article/gem', ['article_id' => '\d+'], false],
            [
                '/{blog}/article/{article_id}',
                '/blog/article/123',
                ['article_id' => '\d+'],
                ['blog' => 'blog', 'article_id' => '123']
            ],
            [
                '/{something}/article/{article_id}',
                '/blog/article/123',
                ['article_id' => '\d+'],
                ['something' => 'blog', 'article_id' => '123']
            ],
            ['/{anything}', '/blog/article/123', [], ['anything' => 'blog/article/123']],
            ['/{anything}', '/sdf', [], ['anything' => 'sdf']],
        ];
    }

    /**
     * @test
     * @param string $exprString
     * @param string $newString
     * @param mixed  $expected
     * @dataProvider tokenizeProvider
     */
    public function tokenize($exprString, $newString, $expected)
    {
        $expr = new ExpressionParser($exprString, new Tokenizer($exprString));
        $this->assertEquals($expected, $expr->tokenize($newString));
    }

    /**
     * return array
     */
    public function tokenizeProvider()
    {
        return [
            ['{abc}/{cfd}', '123/123', ['abc' => '123', 'cfd' => '123']],
            ['blog/article/{id}', 'blog/article/123', ['id' => '123']],
            ['/{anything}', '/sdf', ['anything' => 'sdf']],
        ];
    }

    /**
     * @test
     * @expectedException \Maketok\Util\Exception\ParserException
     * @expectedExceptionMessage String stats from wrong constant.
     */
    public function tokenizeExceptionWrongStart()
    {
        $e = 'blog/article/{id}/cover';
        $expr = new ExpressionParser($e, new Tokenizer($e));
        $expr->tokenize('/cover{id}blog/article/');
    }

    /**
     * @test
     * @expectedException \Maketok\Util\Exception\ParserException
     * @expectedExceptionMessage Can't combine variables, wrong number of placeholders.
     */
    public function tokenizeExceptionVariables()
    {
        $e = '{abc}{cfd}';
        $expr = new ExpressionParser($e, new Tokenizer($e));
        $expr->tokenize('123123');
    }

    /**
     * @test
     * @param string $delimiter
     * @param string $newString
     * @param mixed  $expected
     * @dataProvider splitProvider
     */
    public function strSplit($delimiter, $newString, $expected)
    {
        $this->assertEquals($expected, ExpressionParser::strSplit($delimiter, $newString));
    }

    /**
     * return array
     */
    public function splitProvider()
    {
        return [
            ['1', '123', ['23']],
            ['abs', '13abs331', ['13', '331']],
            [[13, 331], '13abs331', ['abs']],
            [['south', '/', 'cat'], 'north cat loves/hates south dog', ['north ', ' loves', 'hates ', ' dog']],
        ];
    }

    /**
     * @test
     * @expectedException \Maketok\Util\Exception\ParserException
     * @expectedExceptionMessage Wrong delimiter type
     * @dataProvider wrongTypeProvider
     * @param mixed $delimiter
     */
    public function strSplitWrongType($delimiter)
    {
        ExpressionParser::strSplit($delimiter, '');
    }

    /**
     * @test
     * @expectedException \Maketok\Util\Exception\ParserException
     * @expectedExceptionMessage Delimiter $ is not present
     */
    public function strSplitMissingDelimiter()
    {
        ExpressionParser::strSplit(['/', '#', '$'], '/this#string');
    }

    /**
     * @return array
     */
    public function wrongTypeProvider()
    {
        return [
            [new \stdClass()],
            [null],
            [fopen(__FILE__, 'r')]
        ];
    }

    /**
     * @test
     * @dataProvider avProvider
     * @param string $haystack
     * @param string|string[] $delimiter
     * @param bool $expected
     */
    public function arrayValueContains($haystack, $delimiter, $expected)
    {
        $this->assertEquals($expected, ExpressionParser::arrayValueContains($delimiter, $haystack));
    }

    /**
     * @return array
     */
    public function avProvider()
    {
        return [
            ['$adaasd', ['$'], true],
            ['$adaasd', ['#'], false]
        ];
    }
}
