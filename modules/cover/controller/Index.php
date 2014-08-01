<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace modules\cover\controller;

use Maketok\Mvc\Controller\AbstractController;
use Maketok\Util\RequestInterface;

class Index extends AbstractController
{


    public function indexAction(RequestInterface $request)
    {

        $this->setTemplate('base.html.twig');
        return $this->prepareResponse($request, array('title' => 'Main Page'));
    }
}