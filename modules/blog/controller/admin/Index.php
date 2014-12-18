<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
