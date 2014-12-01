<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\admin\controller\admin;

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

        $this->setTemplate('admin.html.twig');
        return $this->prepareResponse($request, array('title' => 'Maketok Admin Dashboard'));
    }
}
