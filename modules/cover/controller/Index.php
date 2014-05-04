<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace modules\cover\controller;

use Maketok\Http\Response;
use Maketok\Util\RequestInterface;

class Index
{


    public function indexAction(RequestInterface $request)
    {
        return Response::create("Hello World");
    }
}