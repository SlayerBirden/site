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
use Maketok\Module\Mvc\AbstractBaseController;
use modules\blog\model\ArticleTable;

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
