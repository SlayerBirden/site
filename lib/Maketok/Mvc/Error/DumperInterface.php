<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
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
