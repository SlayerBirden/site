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

use Maketok\Http\Request;
use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\Exception\ModelInfoException;
use modules\blog\Model\ArticleTable;
use Symfony\Component\Form\FormInterface;

class Article extends AbstractAdminController
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $this->setTemplate('article.html.twig');
        $article = $this->initArticle($request);
        $form = $this->getFormFactory()->create('article', $article);
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->handleArticle($form);
        }
        return $this->prepareResponse($request, array(
            'title' => 'Maketok Admin - Edit Article ' . $article->title,
            'description' => 'Article ' . $article->title,
            'form' => $form->createView()
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        $article = $this->initArticle($request);
        /** @var ArticleTable $articleTable */
        $articleTable = $this->getSC()->get('article_table');
        try {
            $articleTable->delete($article->id);
        } catch (\Exception $e) {
            $this->getSC()->get('logger')->error(sprintf("Could not remove article #%d", $article->id));
        }
        return $this->redirect('/blog');
    }

    /**
     * @param Request $request
     * @return \modules\blog\model\Article
     * @throws RouteException
     */
    protected function initArticle(Request $request)
    {
        $id = $request->getAttributes()->get('id');
        if ($id === null) {
            // route exception will lead to 404
            throw new RouteException("Can not process article without id.");
        }
        /** @var ArticleTable $articleTable */
        $articleTable = $this->getSC()->get('article_table');
        try {
            return $articleTable->find($id);
        } catch (ModelException $e) {
            throw new RouteException("Could not find model by id.");
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $this->setTemplate('article.html.twig');
        $form = $this->getFormFactory()->create('article', null);
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->handleArticle($form);
        }
        return $this->prepareResponse($request, array(
            'title' => 'Maketok Admin - Add New Article ',
            'description' => 'Article Creation',
            'form' => $form->createView()
        ));
    }

    /**
     * handle form request
     * @param FormInterface $form
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function handleArticle(FormInterface $form)
    {
        /** @var ArticleTable $articleTable */
        $articleTable = $this->getSC()->get('article_table');
        try {
            $articleTable->save($form->getData());
            $this->getSession()->getFlashBag()->add(
                'success',
                'The article was saved successfully!'
            );
        } catch (ModelInfoException $e) {
            $this->getSession()->getFlashBag()->add(
                'notice',
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->getLogger()->err($e);
            $this->getSession()->getFlashBag()->add(
                'error',
                'There was an error processing your request. Our specialists will be looking into it.'
            );
            return $this->returnBack();
        }
        return $this->redirect('blog');
    }
}
