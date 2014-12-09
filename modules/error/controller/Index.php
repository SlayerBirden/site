<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\error\controller;

use Maketok\Mvc\Controller\AbstractController;
use Maketok\Util\RequestInterface;
use Maketok\Mvc\Error\DumperInterface;

class Index extends AbstractController implements DumperInterface
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function norouteAction(RequestInterface $request)
    {
        $this->setTemplate('404.html.twig');
        return $this->prepareResponse($request, array('title' => 'Page Not Found'), null, 404);
    }
    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function errorAction(RequestInterface $request)
    {
        $this->setTemplate('500.html.twig');
        $params = array(
            'title' => 'Internal Error',
        );
        return $this->prepareResponse($request, $params, null, 500);
    }
}
