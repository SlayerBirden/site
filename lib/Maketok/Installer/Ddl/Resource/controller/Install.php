<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl\Resource\controller;

use Maketok\Installer\Ddl\Resource\Model\DdlClientType;
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
        $response = new StreamedResponse(function () {
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
        /** @var DdlClientType $clientTable */
        $clientTable = $this->ioc()->get('ddl_client_table');
        $clients = $clientTable->fetchAllWithDependency();
        $this->setTemplate('install-manager.html.twig');
        return $this->prepareResponse($request, array(
            'install_url' => $this->getUrl('install/ddl/run'),
            'title' => 'DDL - Installer - Admin Management Area',
            'description' => 'DDL Installer',
            'clients' => $clients,
        ));
    }
}
