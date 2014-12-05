<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\blog\controller;

use Maketok\Mvc\Controller\AbstractController;
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
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
        $article = $this->initArticle($request);
        $this->setViewDependency(array('base'));
        $this->setTemplate('article.html.twig');
        return $this->prepareResponse($request, array(
            'article' => $article,
        ));
    }

    /**
     * @param RequestInterface $request
     * @return \modules\blog\model\Article
     * @throws RouteException
     */
    protected function initArticle(RequestInterface $request)
    {
        $id = $request->getAttributes()->get('id');
        $code = $request->getAttributes()->get('code');
        if ($id === null && $code === null) {
            throw new RouteException("Can not process article without id or code.");
        }
        /** @var ArticleTable $articleTable */
        $articleTable = $this->getSC()->get('article_table');
        try {
            if (!is_null($code)) {
                return $articleTable->findByCode($code);
            }
            return $articleTable->find($id);
        } catch (ModelException $e) {
            throw new RouteException("Could not find model by id.");
        }
    }
}
