<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace modules\blog\controller\admin;

use Maketok\Module\Mvc\AbstractAdminController;
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
