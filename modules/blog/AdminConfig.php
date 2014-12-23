<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\blog;

use Maketok\Module\AdminConfigInterface;
use Maketok\Mvc\Router\Route\Http\Literal;
use Maketok\Mvc\Router\Route\Http\Parameterized;
use Maketok\Navigation\Link;

/**
 * @codeCoverageIgnore
 */
class AdminConfig extends Config implements AdminConfigInterface
{

    /**
     * {@inheritdoc}
     */
    public function initRoutes()
    {
        $this->getRouter()->addRoute(new Literal('/blog', ['\modules\blog\controller\admin\Index', 'indexAction']));
        $this->getRouter()->addRoute(new Literal('/blog/article/new', ['\modules\blog\controller\admin\Article', 'newAction']));
        $this->getRouter()->addRoute(new Parameterized(
            '/blog/article/edit/{id}',
            ['\modules\blog\controller\admin\Article', 'editAction'],
            [],
            ['id' => '^\d+$']
        ));
        $this->getRouter()->addRoute(new Parameterized(
            '/blog/article/delete/{id}',
            ['\modules\blog\controller\admin\Article', 'deleteAction'],
            [],
            ['id' => '^\d+$']
        ));

        // menu handling
        $this->ioc()->get('topmenu')->addLink(new Link('blog', $this->getUrl('blog'), 6, 'Blog'));
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return true;
    }
}
