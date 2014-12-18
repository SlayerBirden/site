<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Error;

use Maketok\Util\RequestInterface;
use Symfony\Component\HttpFoundation\Response;

interface DumperInterface
{

    /**
     * @param RequestInterface $request
     * @return Response
     */
    public function errorAction(RequestInterface $request);

    /**
     * @param RequestInterface $request
     * @return Response
     */
    public function norouteAction(RequestInterface $request);
}
