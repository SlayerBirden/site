<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace admin\controller;

use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Util\Monolog\Handler\HttpStreamedHandler;
use Maketok\Util\RequestInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Install extends AbstractAdminController
{

    /**
     * @internal param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function runAction()
    {
        /** @var Logger $logger */
        $logger = $this->getSC()->get('logger');
        $handler = new HttpStreamedHandler();
        $handler->setFormatter(new HtmlFormatter());
        $logger->pushHandler($handler);
        $response = new StreamedResponse(function(){
            $this->getSC()->get('installer_ddl_manager')->process();
        });
        return $response;
    }

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(RequestInterface $request)
    {
        $this->setTemplate('install-manager.html.twig');
        return $this->prepareResponse($request, array(
            'install_url' => $this->getUrl('install/run'),
            'title' => 'Installer - Admin Management Area'
        ));
    }
}
