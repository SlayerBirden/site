<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\AbstractManager;
use Maketok\Installer\ConfigReaderInterface;
use Maketok\Installer\Exception;
use Maketok\Installer\ManagerInterface;
use Maketok\Installer\ClientInterface as BaseClientInterface;
use Maketok\Installer\Resource\Model\DdlClientConfig;
use Maketok\Util\AbstractTableMapper;
use Maketok\Util\StreamHandlerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Manager extends AbstractManager implements ManagerInterface
{

    /** @var \Maketok\Installer\Resource\Model\DdlClientConfigType  */
    protected $_tableMapper;

    /**
     * Constructor
     * @param Adapter $adapter
     * @param \Maketok\Util\Zend\Db\Sql\Sql|\Zend\Db\Sql\Sql $sql
     * @param ConfigReaderInterface $reader
     * @param \Maketok\Util\AbstractTableMapper $tableMapper
     * @param StreamHandlerInterface $handler
     */
    public function __construct(Adapter $adapter,
                                Sql $sql,
                                ConfigReaderInterface $reader,
                                AbstractTableMapper $tableMapper,
                                StreamHandlerInterface $handler = null)
    {
        $this->_adapter = $adapter;
        $this->_reader = $reader;
        if (!is_null($handler)) {
            $this->_streamHandler = $handler;
        }
        $this->_sql = $sql;
        $this->_tableMapper = $tableMapper;
        $this->_type = 'ddl';
    }

    /** @var array */
    protected $_directives;

    /**
     * {@inherited}
     */
    public function addClient(BaseClientInterface $client)
    {
        if (!($client instanceof ClientInterface)) {
            throw new Exception("Wrong client type.");
        }
        parent::addClient($client);
    }

    /**
     * @param ClientInterface $client
     * @return bool
     */
    public function validateClient(ClientInterface $client)
    {
        /** @var AbstractClient $client */
        $allConfigs = $this->getAllConfigs($client->getCode($this->_type));
        end($allConfigs);
        $last = key($allConfigs);
        if ($client->getType() == $client::TYPE_INSTALL) {
            return $this->natRecursiveCompare($client->getVersion($this->_type), $last) === 1;
        } elseif ($client->getType() == $client::TYPE_UPDATE) {
            return array_key_exists($client->getNextVersion(), $allConfigs);
        }
        return false;
    }

    /**
     * This is where all clients are processed
     * @return void
     */
    public function process()
    {
        $this->_streamHandler->lock();
        foreach ($this->_clients as $client) {
            if (!$this->validateClient($client)) {
                continue;
            }
            /** @var ClientInterface $client */
            $this->_reader->processConfig($this->getConfigChain($client));
            $this->_reader->validateDirectives();
            $this->mergeDirectives($this->_reader->getDirectives());
        }
        $this->applyDirectives();
    }

    /**
     * @param ClientInterface $client
     * @return array (of configs)
     */
    public function getConfigChain($client)
    {
        /** @var AbstractClient $client */
        $chain = [];
        switch ($client->getType()) {
            case $client::TYPE_INSTALL:
                $chain[] = $this->_tableMapper->getCurrentConfig($client->getCode($this->_type));
                $chain[] = $client->getConfig($this->_type);
                break;
            case $client::TYPE_UPDATE:
                $allConfigs = $this->getAllConfigs($client->getCode($this->_type));
                $currentConfig = $this->_tableMapper->getCurrentConfig($client->getCode($this->_type));
                $currentVersion = $currentConfig->version;
                if ($currentConfig->version > $client->getNextVersion()) {
                    // downgrade
                    foreach ($allConfigs as $version => $config) {
                        if ($version >= $client->getNextVersion() &&
                            $version <= $currentVersion) {
                            $chain[] = $config;
                        }
                    }
                    // reverse array to put chain in correct order
                    $chain = array_reverse($chain);
                } else {
                    // upgrade
                    foreach ($allConfigs as $version => $config) {
                        if ($version >= $currentVersion &&
                            $version <= $client->getNextVersion()) {
                            $chain[] = $config;
                        }
                    }
                }
                break;
        }
        return $chain;
    }

    /**
     * @param $code
     * @return array
     */
    public function getAllConfigs($code)
    {
        $configs = [];
        $configModels = $this->_tableMapper->getAllConfigs($code);
        foreach ($configModels as $cnf) {
            /** @var DdlClientConfig $cnf */
            $configs[$cnf->version] = $cnf->config;
        }
        uksort($configs, array($this, 'natRecursiveCompare'));
        return $configs;
    }

    /**
     * @param array $newDirectives
     * @return array
     */
    public function mergeDirectives(array $newDirectives)
    {
        // @TODO add logic
    }

    /**
     * @return void
     */
    public function applyDirectives()
    {
        // @TODO add logic
    }
}
