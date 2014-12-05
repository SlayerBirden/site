<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;


use Symfony\Component\HttpFoundation\Session\SessionInterface;

interface RequestInterface extends MessageInterface
{
    /**
     * @param SessionInterface $session
     * @return mixed
     */
    public function setSession(SessionInterface $session);

    /**
     * @return SessionInterface
     */
    public function getSession();

    /**
     * @return array|\IteratorAggregate
     */
    public function getAttributes();

    /**
     * @return mixed
     */
    public function getPathInfo();
}
