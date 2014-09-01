<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router\Route\Http\Error;

use Maketok\Util\RequestInterface;
use Symfony\Component\HttpFoundation\Response;

class Dumper
{

    /**
     * default error dumper
     * @param \Maketok\Util\RequestInterface $request
     * @return Response
     */
    public function errorAction(RequestInterface $request)
    {
        $text = 'Oops! We are really sorry, but there was an error!';
        return new Response($text, 500);
    }

    /**
     * @param \Maketok\Util\RequestInterface $request
     * @return Response
     */
    public function norouteAction(RequestInterface $request)
    {
        return new Response("Oops! We couldn't find the page you searched for. Looks like it doesn't exist anymore.", 404);
    }
}
