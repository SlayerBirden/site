<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
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
     * @codeCoverageIgnore
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function traverse(NodeInterface $node = null)
    {
        if (is_null($node)) {
            $node = $this;
        }
        if ($this->isLeaf($node)) {
            return [$node];
        }
        $return = [$node];
        foreach ($node->getChildren() as $child)
        {
            $return = array_merge($return, $child->traverse());
        }
        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $this->parent = null;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isBranch()
    {
        $children = $this->getChildren();
        return count($children) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isRoot()
    {
        return $this->getParent() === null;
    }

    /**
     * {@inheritdoc}
     */
    public function isLeaf()
    {
        $children = $this->getChildren();
        return count($children) == 0;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(NodeInterface $node)
    {
        array_push($this->children, $node);
        $node->setParent($this);
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function getSiblings()
    {
        if ($this->isRoot()) {
            return [];
        }
        $includingSelf = $this->getParent()->getChildren();
        $current = $this;
        return array_values(array_filter($includingSelf, function($node) use ($current) {
            return $node != $current;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getAncestors()
    {
        $ancestors = [];
        $node = $this;
        while (!$node->isRoot()) {
            $node = $node->getParent();
            $ancestors[] = $node;
        }
        return $ancestors;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot()
    {
        $node = $this;
        while (!$node->isRoot()) {
            $node = $node->getParent();
        }
        return $node;
    }

    /**
     * @return mixed
     * @codeCoverageIgnore
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * @param NodeInterface $node
     * @return self
     */
    public function setParent(NodeInterface $node)
    {
        $this->parent = $node;
    }

    /**
     * @param NodeInterface[] $nodes
     * @return mixed
     */
    public function addChildren(array $nodes)
    {
        foreach ($nodes as $node) {
            $this->addChild($node);
        }
    }
}
