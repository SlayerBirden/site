<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Test;


use Maketok\Util\ExpressionParser;

class ExpressionParserTest extends \PHPUnit_Framework_TestCase
{

    public function testEvaluate()
    {
        $expr = new ExpressionParser('/blog/article/{article_id}');
        $this->assertEquals('/blog/article/3', $expr->evaluate(array(
            'article_id' => 3,
        )));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage One of the parameters (article_id) failed to satisfy requirements.
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
     * @expectedException \Exception
     * @expectedExceptionMessage Can not end up in variable.
     */
    public function testTokenizeError()
    {
        $expr = new ExpressionParser('/blog/article/{article_id');
        $expr->tokenize();
    }

    public function testParse()
    {
        $expr = new ExpressionParser('/blog/article/{article_id}');
        $return = $expr->parse('/blog/article/123');
        $this->assertEquals(array('article_id' => '123'), $return);

        $expr = new ExpressionParser('/blog/article/{article_id}/cat');
        $return = $expr->parse('/blog/article/123/cat');
        $this->assertEquals(array('article_id' => '123'), $return);

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
    }
}
