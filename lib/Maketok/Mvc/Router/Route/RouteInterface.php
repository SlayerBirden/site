<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router\Route;

use Maketok\Util\RequestInterface;

interface RouteInterface
{

    /**
     * @param RequestInterface $request
     * @return Success
     */
    public function match(RequestInterface $request);

    /**
     * @param array $params
     * @return string
     */
    public function assemble(array $params = array());

    /**
     * @return RequestInterface
     */
    public function getRequest();

    /**
     * @return array
     */
    public function getParameters();
}
