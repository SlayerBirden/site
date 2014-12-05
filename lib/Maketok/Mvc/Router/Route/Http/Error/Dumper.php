<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router\Route\Http\Error;

use Symfony\Component\HttpFoundation\Response;

class Dumper
{

    /**
     * default error dumper
     * @internal param RequestInterface $request
     * @return Response
     */
    public function errorAction()
    {
        $text = 'Oops! We are really sorry, but there was an error!';
        return new Response($text, 500);
    }

    /**
     * @internal param RequestInterface $request
     * @return Response
     */
    public function norouteAction()
    {
        return new Response("Oops! We couldn't find the page you searched for. Looks like it doesn't exist anymore.", 404);
    }
}
