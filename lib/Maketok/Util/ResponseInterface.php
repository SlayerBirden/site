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

interface ResponseInterface extends MessageInterface
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function send();
}
