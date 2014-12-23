<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Navigation\Test;

use Maketok\Navigation\Node;

/**
 * @coversDefaultClass \Maketok\Navigation\Node
 */
class NodeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers ::traverse
     * @covers ::addChildren
     */
    public function traverse()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $node->addChildren([$nodeB, $nodeC]);

        $this->assertEquals([$nodeC], $nodeC->traverse());
        $this->assertEquals([$node, $nodeB, $nodeC], $node->traverse());
    }

    /**
     * @test
     * @covers ::detach
     * @covers ::addChild
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
     * @covers ::removeChild
     * @covers ::addChild
     */
    public function removeChild()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $node->addChild($nodeB);

        $node->removeChild($nodeB);
        $this->assertEmpty($node->getChildren());
        $this->assertNull($nodeB->getParent());
    }

    /**
     * @test
     * @covers ::isBranch
     * @covers ::addChild
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
     * @covers ::isRoot
     * @covers ::addChild
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
     * @covers ::isLeaf
     * @covers ::addChild
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
     * @covers ::getChildren
     * @covers ::addChild
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
     * @covers ::getSiblings
     * @covers ::addChildren
     */
    public function getSiblings()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $node->addChildren([$nodeB, $nodeC]);

        $this->assertEquals([$nodeB], $nodeC->getSiblings());
        $this->assertEquals([$nodeC], $nodeB->getSiblings());
        $this->assertEquals([], $node->getSiblings());
    }

    /**
     * @test
     * @covers ::getParent
     * @covers ::addChild
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
     * @covers ::getAncestors
     * @covers ::addChild
     */
    public function getAncestors()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $node->addChild($nodeB)->addChild($nodeC);

        $this->assertEquals([$nodeB, $node], $nodeC->getAncestors());
    }

    /**
     * @test
     * @covers ::getRoot
     * @covers ::addChild
     */
    public function getRoot()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $node->addChild($nodeB)->addChild($nodeC);

        $this->assertEquals($node, $nodeC->getRoot());

        $node = new Node('A');
        $this->assertEquals($node, $node->getRoot());
    }

    /**
     * @test
     * @covers ::setParent
     * @covers ::addChild
     */
    public function setParent()
    {
        $node = new Node('A');
        $nodeB = new Node('B');
        $node->addChild($nodeB);

        $this->assertEquals($node, $nodeB->getParent());
    }
}
