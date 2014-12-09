<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Navigation\Test;

use Maketok\Template\Navigation\Node;

class NodeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::traverse
     */
    public function traverse()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $node->addChild($nodeB);
        $node->addChild($nodeC);

        $this->assertEquals([$nodeC], $nodeC->traverse());
        $this->assertEquals([$node, $nodeB, $nodeC], $node->traverse());
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::detach
     */
    public function detach()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $node->addChild($nodeB);

        $nodeB->detach();
        $this->assertNull($nodeB->getParent());
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::isBranch
     */
    public function isBranch()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $node->addChild($nodeB);

        $this->assertTrue($node->isBranch());
        $this->assertFalse($nodeB->isBranch());
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::isRoot
     */
    public function isRoot()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $node->addChild($nodeB);

        $this->assertTrue($node->isRoot());
        $this->assertFalse($nodeB->isRoot());
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::isLeaf
     */
    public function isLeaf()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $node->addChild($nodeB);

        $this->assertFalse($node->isLeaf());
        $this->assertTrue($nodeB->isLeaf());
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::addChild
     */
    public function addChild()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $node->addChild($nodeB);

        $this->assertEquals($node, $nodeB->getParent());
        $this->assertEquals([$nodeB], $node->getChildren());
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::getChildren
     */
    public function getChildren()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $node->addChild($nodeB);

        $this->assertEquals([$nodeB], $node->getChildren());
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::getSiblings
     */
    public function getSiblings()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $node->addChild($nodeB);
        $node->addChild($nodeC);

        $this->assertEquals([$nodeB], $nodeC->getSiblings());
        $this->assertEquals([$nodeC], $nodeB->getSiblings());
        $this->assertEquals([], $node->getSiblings());
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::getParent
     */
    public function getParent()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $node->addChild($nodeB);

        $this->assertEquals($node, $nodeB->getParent());
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::getAncestors
     */
    public function getAncestors()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $node->addChild($nodeB);
        $nodeB->addChild($nodeC);

        $this->assertEquals([$nodeB, $node], $nodeC->getAncestors());
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::getRoot
     */
    public function getRoot()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $node->addChild($nodeB);
        $nodeB->addChild($nodeC);

        $this->assertEquals($node, $nodeC->getRoot());

        $node = new Node('A');
        $this->assertEquals($node, $node->getRoot());
    }

    /**
     * @test
     * @covers Maketok\Template\Navigation\Node::setParent
     */
    public function setParent()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $node->addChild($nodeB);

        $this->assertEquals($node, $nodeB->getParent());
    }
}