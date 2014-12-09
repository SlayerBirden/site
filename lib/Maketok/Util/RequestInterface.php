<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
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
     * @return array|\IteratorAggregate|\Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getAttributes();

    /**
     * @return mixed
     */
    public function getPathInfo();
}
