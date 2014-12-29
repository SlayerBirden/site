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

use Maketok\Http\Request;
use Maketok\Installer\Ddl\Resource\Model\DdlClient;
use Maketok\Installer\Ddl\Resource\Model\DdlClientType;
use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Util\Monolog\Handler\HttpStreamedHandler;
use Maketok\Util\RequestInterface;
use Maketok\Util\VersionComparer;
use Monolog\Formatter\HtmlFormatter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Install extends AbstractAdminController
{
    /**
     * @internal param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function runAction()
    {
        $logger = $this->getLogger();
        $handler = new HttpStreamedHandler();
        $handler->setFormatter(new HtmlFormatter());
        $logger->pushHandler($handler);
        $response = new StreamedResponse(function () {
            $this->ioc()->get('installer_ddl_manager')->process();
        });
        return $response;
    }

    /**
     * @param Request $request
     * @return \Maketok\Http\Response
     */
    public function updatetoAction(Request $request)
    {
        $type = $request->getAttributes()->get('type');
        $id = $request->getAttributes()->get('id');
        // so far only few types supported
        try {
            $clientModel = $this->initClient($id);
            /** @var DdlClientType $clientTable */
            $clientTable = $this->ioc()->get('ddl_client_table');
            switch ($type) {
                case 'last':
                    //
                    break;
                case 'software':
                    $clientModel->version = $this->getSoftwareVersion($clientModel);
                    $clientTable->save($clientModel);
                    break;
            }
            $this->addSessionMessage('success', "Successful update. Please run the installer now.");
        } catch (\Exception $e) {
            $this->getLogger()->emerg($e->__toString());
            $this->addSessionMessage('error', sprintf("There was an error\n%s.", $e->getMessage()));
        }
        return $this->returnBack();
    }

    /**
     * @param DdlClient $client
     * @return string
     */
    protected function getSoftwareVersion(DdlClient $client)
    {
        /** @var \Maketok\Installer\Ddl\Manager $manager */
        $manager = $this->ioc()->get('installer_ddl_manager');
        $clients = $manager->getSoftwareClients();
        foreach ($clients as $sClient) {
            if ($client->code == $sClient->getDdlCode()) {
                return $sClient->getDdlVersion();
            }
        }
        return $client->version;
    }

    /**
     * @param int $id
     * @return DdlClient
     */
    protected function initClient($id)
    {
        /** @var DdlClientType $clientTable */
        $clientTable = $this->ioc()->get('ddl_client_table');
        return $clientTable->find($id);
    }

    /**
     * @param RequestInterface $request
     * @return \Maketok\Http\Response
     */
    public function indexAction(RequestInterface $request)
    {
        try {
            /** @var DdlClientType $clientTable */
            $clientTable = $this->ioc()->get('ddl_client_table');
            $clients = $clientTable->fetchAllWithDependency();
        } catch (\Exception $e) {
            $this->getLogger()->emerg($e->__toString());
            $clients = [];
        }
        $templateClients = [];
        /** @var DdlClient[] $clients */
        foreach ($clients as $client) {
            $comparer = new VersionComparer();
            $client->got_update = $comparer->natRecursiveCompare(
                    $client->version,
                    $this->getSoftwareVersion($client)
                ) === -1;
            $templateClients[] = $client;
        }
        $this->setTemplate('install-manager.html.twig');
        return $this->prepareResponse($request, array(
            'install_url' => $this->getUrl('install/ddl/run'),
            'title' => 'DDL - Installer - Admin Management Area',
            'description' => 'DDL Installer',
            'clients' => $templateClients,
        ));
    }
}
