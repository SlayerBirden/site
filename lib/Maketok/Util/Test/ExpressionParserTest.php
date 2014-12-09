<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Test;


use Maketok\Util\ExpressionParser;

class ExpressionParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @throws \Maketok\Util\Exception\ParserException
     * @covers Maketok\Util\ExpressionParser::evaluate
     */
    public function testEvaluate()
    {
        $expr = new ExpressionParser('/blog/article/{article_id}');
        $this->assertEquals('/blog/article/3', $expr->evaluate(array(
            'article_id' => 3,
        )));
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage One of the parameters (article_id) failed to satisfy requirements.
     * @covers Maketok\Util\ExpressionParser::evaluate
     */
    public function testEvaluateFail()
    {
        $expr = new ExpressionParser('/blog/article/{article_id}');
        $this->assertEquals('/blog/article/sdf', $expr->evaluate(array(
            'article_id' => 'sdf',
        ), array(
            'article_id' => '\d+'
        )));
    }

    /**
     * @test
     * @throws \Maketok\Util\Exception\ParserException
     * @covers Maketok\Util\ExpressionParser::tokenize
     */
    public function testTokenize()
    {
        $expr = new ExpressionParser('/blog/article/{article_id}');
        $tokenized = $expr->tokenize();
        $this->assertEquals(array(
            array(
                'type' => 'const',
                'val' => '/blog/article/',
            ),
            array(
                'type' => 'var',
                'val' => 'article_id',
            )
        ), $tokenized);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Can not end up in variable.
     * @covers Maketok\Util\ExpressionParser::tokenize
     */
    public function testTokenizeError()
    {
        $expr = new ExpressionParser('/blog/article/{article_id');
        $expr->tokenize();
    }

    /**
     * @test
     * @throws \Maketok\Util\Exception\ParserException
     * @covers Maketok\Util\ExpressionParser::parse
     */
    public function testParse()
    {
        $expr = new ExpressionParser('/blog/article/{article_id}');
        $return = $expr->parse('/blog/article/123');
        $this->assertEquals(array('article_id' => '123'), $return);

        $expr = new ExpressionParser('/blog/article/{article_id}/cat');
        $return = $expr->parse('/blog/article/123/cat');
        $this->assertEquals(array('article_id' => '123'), $return);

        $expr = new ExpressionParser('/blog/article/{article_id}/dog');
        $return = $expr->parse('/blog/article/123/cat');
        $this->assertFalse($return);

        $expr = new ExpressionParser('/blog/article');
        $return = $expr->parse('/blog/article');
        $this->assertEquals([], $return);

        $expr = new ExpressionParser('/blog/article/{article_id}');
        $return = $expr->parse('/blog/article/');
        $this->assertFalse($return);

        $expr = new ExpressionParser('/blog/article/{article_id}');
        $return = $expr->parse('/blog/article/gem', array('article_id' => '\d+'));
        $this->assertFalse($return);

        $expr = new ExpressionParser('/{blog}/article/{article_id}');
        $return = $expr->parse('/blog/article/123', array('article_id' => '\d+'));
        $this->assertEquals(array(
            'blog' => 'blog',
            'article_id' => '123'
        ), $return);

        $expr = new ExpressionParser('/{something}/article/{article_id}');
        $return = $expr->parse('/blog/article/123', array('article_id' => '\d+'));
        $this->assertEquals(array(
            'something' => 'blog',
            'article_id' => '123'
        ), $return);

        $expr = new ExpressionParser('/{anything}');
        $return = $expr->parse('/blog/article/123');
        $this->assertEquals(array(
            'anything' => 'blog/article/123',
        ), $return);

        $expr = new ExpressionParser('/{anything}');
        $return = $expr->parse('/sdf');
        $this->assertEquals(array(
            'anything' => 'sdf',
        ), $return);
    }
}
