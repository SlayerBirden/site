<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\error\controller;

use Maketok\Http\Request;
use Maketok\Mvc\Controller\AbstractBaseController;
use Maketok\Mvc\Error\DumperTrait;

class Index extends AbstractBaseController
{
    use DumperTrait;

    /**
     * {@inheritdoc}
     */
    public function norouteAction(Request $request)
    {
        $this->setTemplate('404.html.twig');
        return $this->prepareResponse($request, array('title' => 'Page Not Found'), null, 404);
    }

    /**
     * {@inheritdoc}
     */
    public function errorAction(Request $request)
    {
        $this->setTemplate('500.html.twig');
        $params = array(
            'title' => 'Internal Error',
        );
        return $this->prepareResponse($request, $params, null, 500);
    }
}
