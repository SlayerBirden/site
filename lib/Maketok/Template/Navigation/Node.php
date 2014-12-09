<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Navigation;

class Node implements NodeInterface
{

    /**
     * @var NodeInterface
     */
    protected $parent;
    /**
     * @var array
     */
    protected $children = [];
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * pre-order traverse from the root
     * @return NodeInterface[]
     */
    public function traverse()
    {
        // TODO: Implement traverse() method.
    }

    /**
     * detach current node from parent
     * @return self
     */
    public function detach()
    {
        // TODO: Implement detach() method.
    }

    /**
     * check if current node is a branch
     * @return bool
     */
    public function isBranch()
    {
        // TODO: Implement isBranch() method.
    }

    /**
     * check if current node is root
     * @return bool
     */
    public function isRoot()
    {
        // TODO: Implement isRoot() method.
    }

    /**
     * check if current node is leaf
     * @return bool
     */
    public function isLeaf()
    {
        // TODO: Implement isLeaf() method.
    }

    /**
     * add Child Node
     * @param NodeInterface $link
     * @return self
     */
    public function addChild(NodeInterface $link)
    {
        // TODO: Implement addChild() method.
    }

    /**
     * get Children
     * @return NodeInterface[]
     */
    public function getChildren()
    {
        // TODO: Implement getChildren() method.
    }

    /**
     * get Siblings of current node
     * not including current node
     * @return NodeInterface[]
     */
    public function getSiblings()
    {
        // TODO: Implement getSiblings() method.
    }

    /**
     * get Parent
     * @return NodeInterface
     */
    public function getParent()
    {
        // TODO: Implement getParent() method.
    }

    /**
     * get Ancestors In descending order
     * @return NodeInterface[]
     */
    public function getAncestors()
    {
        // TODO: Implement getAncestors() method.
    }

    /**
     * @return NodeInterface
     */
    public function getRoot()
    {
        // TODO: Implement getRoot() method.
    }
}
