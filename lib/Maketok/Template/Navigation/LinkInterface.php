<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Navigation;

interface LinkInterface extends NodeInterface
{

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
     * create new link
     * @param string $code
     * @param string $reference
     * @param int $order
     * @param LinkInterface $parent
     */
    public function __construct($code, $reference = null, $order = null, LinkInterface $parent = null);

    /**
     * add child with order
     * @param LinkInterface $link
     * @return self
     */
    public function addOrderedChild(LinkInterface $link);

    /**
     * find a link by code within current tree
     * @param string $code
     * @return LinkInterface
     */
    public function findLink($code);
}
