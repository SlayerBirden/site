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

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Tree\Node;
use Zend\Uri\UriFactory;

/**
 * Class Link
 * @package Maketok\Navigation
 * @method LinkInterface[] traverse(\Maketok\Tree\NodeInterface $node = null)
 * @method LinkInterface getRoot
 * @method LinkInterface addChild(\Maketok\Tree\NodeInterface $node)
 */
class Link extends Node implements LinkInterface
{
    use UtilityHelperTrait;
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
        if (is_callable($this->title)) {
            $this->title = call_user_func($this->title);
        }
        if ($parent) {
            $parent->addChild($this);
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
                'code' => $link->getCode(),
                'href' => $link->getReference(),
                'title' => $link->getTitle(),
                'children' => [],
                'is_active' => $this->isActive(),
                'full_reference' => $this->getFullReference(),
            ]
        ];
        foreach ($link->getChildren() as $child) {
            $res[$link->getCode()]['children'] = array_merge($res[$link->getCode()]['children'], $child->asArray());
        }

        return $res;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $baseUri = UriFactory::factory($this->ioc()->getParameter('base_url'));
        $uriRef = UriFactory::factory((string) $this->getFullReference());
        $currentUri = UriFactory::factory((string) $this->getCurrentUrl());

        $equalHosts = $uriRef->getHost() === $currentUri->getHost();
        $strippedCurrentPath = str_replace($baseUri->getPath(), '', $currentUri->getPath());
        $strippedRefPath = str_replace($baseUri->getPath(), '', $uriRef->getPath());
        $currentContainsStripped = !empty($strippedRefPath)
            && ($strippedRefPath !== '/')
            && strpos($strippedCurrentPath, $strippedRefPath) !== false;
        $pathsEquals = $uriRef->getPath() == $currentUri->getPath();

        return $equalHosts && ($currentContainsStripped || $pathsEquals);
    }

    /**
     * @return string
     */
    public function getFullReference()
    {
        $href = $this->getReference();
        // determine what kind of data we have
        $uri = UriFactory::factory((string) $href);
        if ($uri->isValid() && $uri->isAbsolute()) {
            return $uri->toString();
        } elseif ($uri->isValidRelative()) {
            return $this->getUrl($uri->getPath());
        } else {
            return '';
        }
    }
}
