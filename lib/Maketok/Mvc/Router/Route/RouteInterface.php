<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Mvc\Router\Route;

use Maketok\Util\RequestInterface;

interface RouteInterface
{
    public function match(RequestInterface $request);

    public function assemble(array $params);
}