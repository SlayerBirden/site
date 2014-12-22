<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Navigation;

/**
 * Class Link
 * @package Maketok\Navigation
 * @method LinkInterface[] traverse(NodeInterface $node = null)
 * @method LinkInterface getRoot
 * @method LinkInterface addChild(NodeInterface $node)
 */
class Link extends Node implements LinkInterface
{
    /**
     * @var mixed|string
     */
    private $code;
    /**
     * @var null
     */
    private $reference;
    /**
     * @var null
     */
    private $order;
    /**
     * @var null
     */
    private $title;

    /**
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    public function __construct($code, $reference = null, $order = null, $title = null, LinkInterface $parent = null)
    {
        $this->code = $code;
        $this->reference = $reference;
        $this->order = $order;
        $this->title = $title;
        if ($parent) {
            $this->setParent($parent);
        }
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function __toString()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setReference($href)
    {
        $this->reference = $href;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * {@inheritdoc}
     */
    public function find($code)
    {
        $nodes = $this->traverse($this->getRoot());
        foreach ($nodes as $node) {
            if ($code == $node->getCode()) {
                return $node;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findLink(LinkInterface $link)
    {
        return $this->find($link->getCode());
    }

    /**
     * @return LinkInterface[]
     */
    public function getChildren()
    {
        $children = parent::getChildren();
        // @codeCoverageIgnoreStart
        usort($children, function (LinkInterface $a, LinkInterface $b) {
            if ($a->getOrder() > $b->getOrder()) {
                return 1;
            } elseif ($a->getOrder() < $b->getOrder()) {
                return -1;
            }

            return 0;
        });
        // @codeCoverageIgnoreEnd
        return $children;
    }

    /**
     * {@inheritdoc}
     */
    public function asArray(LinkInterface $link = null)
    {
        if (is_null($link)) {
            $link = $this;
        }
        $res = [
            $link->getCode() => [
                'href' => $link->getReference(),
                'title' => $link->getTitle(),
                'children' => []
            ]
        ];
        foreach ($link->getChildren() as $child) {
            $res[$link->getCode()]['children'] = array_merge($res[$link->getCode()]['children'], $child->asArray());
        }

        return $res;
    }
}
