<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @return array|\IteratorAggregate|\Symfony\Component\HttpFoundation\HeaderBag
     */
    public function getHeaders();

    /**
     * @return mixed
     */
    public function getPathInfo();
}
