<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace admin\manager\controller;

use Maketok\Mvc\Controller\AbstractController;
use Maketok\Util\RequestInterface;

class Index extends AbstractController
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(RequestInterface $request)
    {
        $this->setTemplate('admin-manager.html.twig');
        return $this->prepareResponse($request, array('title' => 'Admin Manager'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _getTemplatePath($template = null, $module = null)
    {
        if (is_null($template)) {
            $template = $this->_template;
        }
        return AR . "/admin/manager/templates/$template";
    }
}