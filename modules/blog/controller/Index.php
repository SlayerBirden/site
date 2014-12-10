<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace modules\blog\controller;

use Maketok\Mvc\Controller\AbstractController;
use Maketok\Util\RequestInterface;
use modules\blog\model\ArticleTable;

class Index extends AbstractController
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(RequestInterface $request)
    {
        $this->setTemplate('blog.html.twig');
        /** @var ArticleTable $articleTable */
        $articleTable = $this->getSC()->get('article_table');
        $articles = $articleTable->getTenMostRecent();
        return $this->prepareResponse($request, array(
            'title' => 'Blog',
            'description' => '10 Most Recent Articles:',
            'articles' => $articles
        ));
    }

}
