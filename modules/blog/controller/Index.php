<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace modules\blog\controller;

use Maketok\Mvc\Controller\AbstractController;
use Maketok\Util\RequestInterface;

class Index extends AbstractController
{


    public function indexAction(RequestInterface $request)
    {
        $this->setDependency(array('cover'));
        $this->setTemplate('blog.html');
        return $this->prepareResponse($request, array(
            'title' => 'Blog',
            'description' => 'Below is the Blog!'
        ));
    }

}