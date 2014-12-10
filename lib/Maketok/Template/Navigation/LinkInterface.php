<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Navigation;

interface LinkInterface extends NodeInterface
{

    /**
     * create new link
     * @param string $code
     * @param string $reference
     * @param int $order
     * @param string $title
     * @param LinkInterface $parent
     */
    public function __construct($code, $reference = null, $order = null, $title = null, LinkInterface $parent = null);

    /**
     * set link order
     * @param int $order
     * @return self
     */
    public function setOrder($order);

    /**
     * get link order
     * @return int
     */
    public function getOrder();

    /**
     * set link title
     * @param string $title
     * @return self
     */
    public function setTitle($title);

    /**
     * get link title
     * @return string
     */
    public function getTitle();

    /**
     * set code
     * @param string $code
     * @return self
     */
    public function setCode($code);

    /**
     * get code
     * @return string
     */
    public function getCode();

    /**
     * set href
     * @param string $href
     * @return self
     */
    public function setReference($href);

    /**
     * get href
     * @return string
     */
    public function getReference();

    /**
     * find a link within current tree
     * @param LinkInterface $link
     * @return LinkInterface|null
     */
    public function findLink(LinkInterface $link);

    /**
     * find a link by code within current tree
     * @param string $code
     * @return LinkInterface|null
     */
    public function find($code);

    /**
     * get Tree representation in array form
     * @param LinkInterface $link
     * @return array
     */
    public function asArray(LinkInterface $link = null);
}
