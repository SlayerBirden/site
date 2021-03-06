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
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
use modules\blog\model\ArticleTable;

class Article extends AbstractBaseController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $article = $this->initArticle($request);
        $this->setTemplate('article.html.twig');
        return $this->prepareResponse($request, array(
            'article' => $article,
        ));
    }

    /**
     * @param Request $request
     * @return \modules\blog\model\Article
     * @throws RouteException
     */
    protected function initArticle(Request $request)
    {
        $code = $request->getAttributes()->get('code');
        if ($code === null) {
            throw new RouteException("Can not process article without code.");
        }
        /** @var ArticleTable $articleTable */
        $articleTable = $this->getSC()->get('article_table');
        try {
            return $articleTable->findByCode($code);
        } catch (ModelException $e) {
            throw new RouteException("Could not find model by id.");
        }
    }
}
