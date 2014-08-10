<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace modules\blog\controller;

use Maketok\App\Site;
use Maketok\Mvc\Controller\AbstractController;
use Maketok\Util\RequestInterface;
use modules\blog\model\ArticleTable;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\ObjectProperty;

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
            'title' => $article->title,
            'content' => $article->content,
            'author' => $article->author,
            'created_at' => $article->created_at,
            'updated_at' => $article->updated_at,
            'id' => $article->id,
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
        if ($id === null) {
            throw new \Exception("Can not process article without id.");
        }
        $articleTable = $this->getSC()->get('article_table');
        return $articleTable->find($id);
    }
}