<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\blog;


use Maketok\App\Site;
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
        Site::getServiceContainer()->get('router')->addRoute(new Literal('/blog', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\admin\\Index',
            'action' => 'index',
        )));
        Site::getServiceContainer()->get('router')->addRoute(new Literal('/blog/article/new', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\admin\\Article',
            'action' => 'new',
        )));
        Site::getServiceContainer()->get('router')->addRoute(new Parameterized('/blog/article/edit/{id}', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\admin\\Article',
            'action' => 'edit',
        ), [], ['id' => '^\d+$']));
        Site::getServiceContainer()->get('router')->addRoute(new Parameterized('/blog/article/delete/{id}', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\admin\\Article',
            'action' => 'delete',
        ), [], ['id' => '^\d+$']));
    }
}
