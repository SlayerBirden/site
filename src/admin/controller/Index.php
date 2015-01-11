<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace admin\controller;

use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Util\RequestInterface;

class Index extends AbstractAdminController
{
    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(RequestInterface $request)
    {
        $this->setTemplate('base-manager.html.twig');
        return $this->prepareResponse($request, array(
            'title' => 'Admin Management Area',
            'description' => 'Dashboard',
        ));
    }
}
