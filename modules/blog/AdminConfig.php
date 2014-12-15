<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace modules\blog;


use Maketok\Module\AdminConfigInterface;
use Maketok\Mvc\Router\Route\Http\Literal;
use Maketok\Mvc\Router\Route\Http\Parameterized;
use modules\blog\controller\admin\Article;
use modules\blog\controller\admin\Index;

class AdminConfig extends Config implements AdminConfigInterface
{

    /**
     * {@inheritdoc}
     */
    public function initRoutes()
    {
        $this->getRouter()->addRoute(new Literal('/blog', [new Index(), 'indexAction']));
        $this->getRouter()->addRoute(new Literal('/blog/article/new', [new Article(), 'newAction']));
        $this->getRouter()->addRoute(new Parameterized(
            '/blog/article/edit/{id}',
            [new Article(), 'editAction'],
            [],
            ['id' => '^\d+$']
        ));
        $this->getRouter()->addRoute(new Parameterized(
            '/blog/article/delete/{id}',
            [new Article(), 'deleteAction'],
            [],
            ['id' => '^\d+$']
        ));
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return true;
    }
}
