<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\blog\controller;

use Maketok\Http\Request;
use Maketok\Mvc\Controller\AbstractBaseController;
use modules\blog\Model\ArticleTable;

class Index extends AbstractBaseController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
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
