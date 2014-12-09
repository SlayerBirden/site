<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Navigation;

interface NodeInterface
{

    /**
     * pre-order traverse
     * @return NodeInterface[]
     */
    public function traverse();

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
     * @param NodeInterface $link
     * @return self
     */
    public function addChild(NodeInterface $link);

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
     * @return NodeInterface
     */
    public function getParent();

    /**
     * get Ancestors In descending order
     * @return NodeInterface[]
     */
    public function getAncestors();

    /**
     * @return NodeInterface
     */
    public function getRoot();
}
