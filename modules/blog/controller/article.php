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

class Article extends AbstractController
{


    public function indexAction(RequestInterface $request)
    {
        $article = $this->_initArticle();
        $this->setTemplate('article.html');
        return $this->prepareResponse($request, array(
            'title' => $article->name,
        ));
    }

    protected function _initArticle()
    {

    }
}