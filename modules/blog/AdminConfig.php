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

class AdminConfig extends Config implements AdminConfigInterface
{

    /**
     * {@inheritdoc}
     */
    public function initRoutes()
    {
        $this->ioc()->get('router')->addRoute(new Literal('/blog', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\admin\\Index',
            'action' => 'index',
        )));
        $this->ioc()->get('router')->addRoute(new Literal('/blog/article/new', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\admin\\Article',
            'action' => 'new',
        )));
        $this->ioc()->get('router')->addRoute(new Parameterized('/blog/article/edit/{id}', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\admin\\Article',
            'action' => 'edit',
        ), [], ['id' => '^\d+$']));
        $this->ioc()->get('router')->addRoute(new Parameterized('/blog/article/delete/{id}', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\admin\\Article',
            'action' => 'delete',
        ), [], ['id' => '^\d+$']));
    }
}
