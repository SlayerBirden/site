<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace admin\controller;

use Maketok\Util\RequestInterface;

class Install extends AbstractController
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function runAction(RequestInterface $request)
    {
        $this->setTemplate('install-manager.html.twig');
        return $this->prepareResponse($request, array());
    }
}
