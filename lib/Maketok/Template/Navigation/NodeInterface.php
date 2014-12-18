<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Template\Navigation;

interface NodeInterface
{

    /**
     * pre-order traverse
     * @param  NodeInterface   $node
     * @return NodeInterface[]
     */
    public function traverse(NodeInterface $node = null);

    /**
     * detach current node from parent
     * @return self
     */
    public function detach();

    /**
     * check if current node is a branch
     * @return bool
     */
    public function isBranch();

    /**
     * check if current node is root
     * @return bool
     */
    public function isRoot();

    /**
     * check if current node is leaf
     * @return bool
     */
    public function isLeaf();

    /**
     * add Child Node
     * @param  NodeInterface $node
     * @return NodeInterface child
     */
    public function addChild(NodeInterface $node);

    /**
     * get Children
     * @return NodeInterface[]
     */
    public function getChildren();

    /**
     * get Siblings of current node
     * not including current node
     * @return NodeInterface[]
     */
    public function getSiblings();

    /**
     * get Parent
     * @return NodeInterface|null
     */
    public function getParent();

    /**
     * @param  NodeInterface $node
     * @return self
     */
    public function setParent(NodeInterface $node);

    /**
     * get Ancestors In descending order
     * @return NodeInterface[]
     */
    public function getAncestors();

    /**
     * @return NodeInterface
     */
    public function getRoot();

    /**
     * @param  NodeInterface[] $nodes
     * @return mixed
     */
    public function addChildren(array $nodes);
}
