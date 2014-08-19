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
use modules\blog\model\ArticleTable;

class Article extends AbstractController
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(RequestInterface $request)
    {
        $article = $this->_initArticle($request);
        $this->setDependency(array('cover'));
        $this->setTemplate('article.html.twig');
        return $this->prepareResponse($request, array(
            'article' => $article,
        ));
    }

    /**
     * @param RequestInterface $request
     * @return \modules\blog\model\Article
     * @throws \Exception
     */
    protected function _initArticle(RequestInterface $request)
    {
        $id = $request->attributes->get('id');
        $code = $request->attributes->get('code');
        if ($id === null && $code === null) {
            throw new \Exception("Can not process article without id or code.");
        }
        /** @var ArticleTable $articleTable */
        $articleTable = $this->getSC()->get('article_table');
        if (!is_null($code)) {
            return $articleTable->findByCode($code);
        }
        return $articleTable->find($id);
    }
}
