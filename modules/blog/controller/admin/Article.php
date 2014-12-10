<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace modules\blog\controller\admin;

use Maketok\App\Site;
use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\Exception\ModelInfoException;
use Maketok\Util\RequestInterface;
use modules\blog\model\ArticleTable;
use Symfony\Component\Form\FormInterface;

class Article extends AbstractAdminController
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(RequestInterface $request)
    {
        $this->setTemplate('article.html.twig');
        $article = $this->initArticle($request);
        $form = $this->getFormFactory()->create('article', $article);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->handleArticle($form);
        }
        return $this->prepareResponse($request, array(
            'title' => 'Maketok Admin - Edit Article ' . $article->title,
            'description' => 'Article ' . $article->title,
            'form' => $form->createView()
        ));
    }

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(RequestInterface $request)
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
     * @param RequestInterface $request
     * @return \modules\blog\model\Article
     * @throws RouteException
     */
    protected function initArticle(RequestInterface $request)
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
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(RequestInterface $request)
    {
        $this->setTemplate('article.html.twig');
        $form = $this->getFormFactory()->create('article', null);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->handleArticle($form);
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
            Site::getSession()->getFlashBag()->add(
                'success',
                'The article was saved successfully!'
            );
        } catch (ModelInfoException $e) {
            Site::getSession()->getFlashBag()->add(
                'notice',
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->getSC()->get('logger')->err($e);
            Site::getSession()->getFlashBag()->add(
                'error',
                'There was an error processing your request. Our specialists will be looking into it.'
            );
            return $this->returnBack();
        }
        return $this->redirect('blog');
    }
}
