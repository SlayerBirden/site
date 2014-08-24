<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace modules\blog\controller\admin;

use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\RequestInterface;
use modules\blog\model\ArticleTable;

class Article extends AbstractAdminController
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(RequestInterface $request)
    {
        $this->setDependency(array('cover'));
        $this->setTemplate('article.html.twig');
        $article = $this->_initArticle($request);
        $form = $this->getFormFactory()->create('article', $article, array(
            'action' => $this->getCurrentUrl(),
            'method' => 'POST',
            'attr' => array('back_url' => $this->getUrl('/blog')),
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var ArticleTable $articleTable */
            $articleTable = $this->getSC()->get('article_table');
            $articleTable->save($form->getData());
            // todo success should go in session
            return $this->_redirect('blog');
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
        $article = $this->_initArticle($request);
        /** @var ArticleTable $articleTable */
        $articleTable = $this->getSC()->get('article_table');
        try {
            $articleTable->delete($article->id);
        } catch (\Exception $e) {
            $this->getSC()->get('logger')->error(sprintf("Could not remove article #%d", $article->id));
        }
        return $this->_redirect('/blog');
    }

    /**
     * @param RequestInterface $request
     * @return \modules\blog\model\Article
     * @throws RouteException
     */
    protected function _initArticle(RequestInterface $request)
    {
        $id = $request->attributes->get('id');
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
        $this->setDependency(array('cover'));
        $this->setTemplate('article.html.twig');
        $form = $this->getFormFactory()->create('article', null, array(
            'action' => $this->getUrl('blog/article/new'),
            'method' => 'POST',
            'attr' => array('back_url' => $this->getUrl('/blog')),
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var ArticleTable $articleTable */
            $articleTable = $this->getSC()->get('article_table');
            $articleTable->save($form->getData());
            // todo success should go in session
            return $this->_redirect('blog');
        }
        return $this->prepareResponse($request, array(
            'title' => 'Maketok Admin - Add New Article ',
            'description' => 'Article Creation',
            'form' => $form->createView()
        ));
    }

}
