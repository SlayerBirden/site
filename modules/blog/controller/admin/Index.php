<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\blog\controller\admin;

use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Util\RequestInterface;
use modules\blog\model\ArticleTable;

class Index extends AbstractAdminController
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
        $articles = $articleTable->fetchAll();
        return $this->prepareResponse($request, array(
            'title' => 'Maketok Admin - Blog CRUD Management',
            'description' => 'Articles:',
            'articles' => $articles
        ));
    }

}
