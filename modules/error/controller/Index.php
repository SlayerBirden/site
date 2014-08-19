<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace modules\error\controller;

use Maketok\Mvc\Controller\AbstractController;
use Maketok\Util\RequestInterface;

class Index extends AbstractController
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function norouteAction(RequestInterface $request)
    {
        $this->setDependency(array('cover'));
        $this->setTemplate('404.html.twig');
        return $this->prepareResponse($request, array('title' => 'Page Not Found'), null, 404);
    }
}