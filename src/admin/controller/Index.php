<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace admin\controller;

use Maketok\Util\RequestInterface;

class Index extends AbstractController
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(RequestInterface $request)
    {
        $this->setTemplate('base-manager.html.twig');
        return $this->prepareResponse($request, array(
            'title' => 'Admin Management Area'
        ));
    }
}
