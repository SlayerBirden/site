<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace modules\blog\controller\admin;

use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Util\RequestInterface;
use modules\blog\model\ArticleTable;

class Article extends AbstractAdminController
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(RequestInterface $request)
    {
        $this->setDependency(array('cover'));
        $this->setTemplate('article.html.twig');
        $article = $this->_initArticle($request);
        $form = $this->getFormFactory()->create('article', $article);
        return $this->prepareResponse($request, array(
            'title' => 'Edit Article ' . $article->title,
            'description' => 'Article ' . $article->title,
            'form' => $form->createView()
        ));
    }

    /**
     * @param RequestInterface $request
     * @return \modules\blog\model\Article
     * @throws \Exception
     */
    protected function _initArticle(RequestInterface $request)
    {
        $id = $request->attributes->get('id');
        if ($id === null) {
            throw new \Exception("Can not process article without id or code.");
        }
        /** @var ArticleTable $articleTable */
        $articleTable = $this->getSC()->get('article_table');
        return $articleTable->find($id);
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
            'action' => $this->getUrl('blog/article/save'),
            'method' => 'POST',
        ));
        return $this->prepareResponse($request, array(
            'title' => 'Add New Article ',
            'description' => 'Article Creation',
            'form' => $form->createView()
        ));
    }

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveAction(RequestInterface $request)
    {
        $form = $this->getFormFactory()->create('article');
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var ArticleTable $articleTable */
            $articleTable = $this->getSC()->get('article_table');
            $articleTable->save($form->getData());
            // todo success should go in session
            return $this->_redirect('blog');
        } else {
            // todo errors should go in session
            $errors = $form->getErrors();
            return $this->_returnBack();
        }
    }
}
