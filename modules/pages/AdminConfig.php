<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\pages;

use Maketok\Mvc\Router\Route\Http\Literal;
use Maketok\Mvc\Router\Route\Http\Parameterized;
use Maketok\Navigation\Link;

class AdminConfig extends Config
{
    /**
     * {@inheritdoc}
     */
    public function initRoutes()
    {
        $this->getRouter()->addRoute(new Literal('/pages', ['\modules\pages\controller\admin\IndexController', 'indexAction']));
        $this->getRouter()->addRoute(new Literal('/pages/new', ['\modules\pages\controller\admin\PageController', 'newAction']));
        $this->getRouter()->addRoute(new Parameterized(
            '/pages/edit/{id}',
            ['\modules\pages\controller\admin\PageController', 'editAction'],
            [],
            ['page_id' => '^\d+$']
        ));
        $this->getRouter()->addRoute(new Parameterized(
            '/pages/delete/{id}',
            ['\modules\pages\controller\admin\PageController', 'deleteAction'],
            [],
            ['page_id' => '^\d+$']
        ));

        // menu handling
        $this->ioc()->get('topmenu')->addLink(new Link('pages', $this->getUrl('pages'), 3, 'Pages'));
    }
    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return true;
    }
}
