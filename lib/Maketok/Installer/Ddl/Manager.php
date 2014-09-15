<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\App\Site;
use Maketok\Installer\AbstractManager;
use Maketok\Installer\ConfigReaderInterface;
use Maketok\Installer\Exception;
use Maketok\Installer\ManagerInterface;
use Maketok\Installer\ClientInterface as BaseClientInterface;
use Maketok\Installer\Ddl\ClientInterface as DdlClientInterface;
use Maketok\Installer\Resource\Model\DdlClient;
use Maketok\Installer\Resource\Model\DdlClientType;
use Maketok\Util\StreamHandlerInterface;

class Manager extends AbstractManager implements ManagerInterface
{

    /** @var Directives */
    private $_directives;
    /**
     * @var ResourceInterface
     */
    protected $_resource;

    /**
     * Constructor
     * @param ConfigReaderInterface $reader
     * @param ResourceInterface $resource
     * @param StreamHandlerInterface $handler
     */
    public function __construct(ConfigReaderInterface $reader,
                                ResourceInterface $resource,
                                StreamHandlerInterface $handler = null)
    {
        $this->_reader = $reader;
        if (!is_null($handler)) {
            $this->_streamHandler = $handler;
        }
        $this->_resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function addClient(BaseClientInterface $client)
    {
        if (!($client instanceof ClientInterface)) {
            throw new Exception("Wrong client type.");
        }
        if (is_null($this->_clients)) {
            $this->_clients = [];
        }
        $this->_clients[$client->getDdlCode()] = $this->getClientModel($client);
    }

    /**
     * @param DdlClientInterface $client
     * @return DdlClient
     */
    public function getClientModel(DdlClientInterface $client)
    {
        try {
            /** @var DdlClientType $type */
            $type = Site::getServiceContainer()->get('ddl_client_table');
            $model = $type->getClientByCode($client->getDdlCode());
        } catch (\Exception $e) {
            // this is kind of a way to catch non existing table request
            // TODO need to find a better way in the future
            $model = new DdlClient();
        }
        $model->code = $client->getDdlCode();
        $model->version = $client->getDdlVersion();
        $model->config = $client->getDdlConfig($model->version);
        $model->dependencies = $client->getDependencies();
        return $model;
    }

    /**
     * This is where all clients are processed
     * @return void
     */
    public function process()
    {
        // lock process
        $this->_streamHandler->lock();
        try {
            // build tree
            $this->_reader->buildDependencyTree($this->_clients);
            // create directives
            $this->createDirectives();
            // create db procedures
            $this->_resource->createProcedures($this->_directives);
            // run
            $this->_resource->runProcedures();
        } catch (\Exception $e) {
            Site::getServiceContainer()
                ->get('logger')
                ->err(sprintf("Exception while running DDL Installer process: %s", $e->__toString()));
        }
        $this->_streamHandler->unLock();
    }

    /**
     * @throws \LogicException
     * @return void
     */
    public function createDirectives()
    {
        if (isset($this->_directives)) {
            // no point in creating directives when they already exist
            return;
        }
        $this->_directives = new Directives();
        $config = $this->_reader->getMergedConfig();
        foreach ($config as $table => $definition) {
            if (!isset($definition['columns']) || !is_array($definition['columns'])) {
                throw new \LogicException(sprintf('Can not have a table `%s` without columns definition.'
                    , $table));
            }
            $dbConfig = $this->_resource->getTable($table);
            // compare def with db
            if (empty($dbConfig)) {
                // add table
                $this->_directives->addProp('addTables', [$table, $definition]);
            } else {
                $_newColumns = $definition['columns'];
                $_oldColumns = $dbConfig['columns'];
                $this->_intelligentCompareColumns($_oldColumns, $_newColumns, $table);
                $_oldConstraints = isset($dbConfig['constraints']) ? $dbConfig['constraints'] : array();
                $_newConstraints = isset($definition['constraints']) ? $definition['constraints'] : array();
                $this->_intelligentCompareConstraints($_oldConstraints, $_newConstraints, $table);
                $_oldIndices = isset($dbConfig['indices']) ? $dbConfig['indices'] : array();
                $_newIndices = isset($definition['indices']) ? $definition['indices'] : array();
                $this->_intelligentCompareIndices($_oldIndices, $_newIndices, $table);
            }
        }
    }

    /**
     * @return Directives
     */
    public function getDirectives()
    {
        return $this->_directives;
    }


    /**
     * @param array $a old
     * @param array $b new
     * @param string $tableName
     * @return array
     */
    protected function _intelligentCompareColumns(array $a, array $b, $tableName)
    {
        $_changeMap = [];
        foreach ($b as $columnName => $columnDefinition) {
            if (!array_key_exists($columnName, $a) && !isset($columnDefinition['old_name'])) {
                $this->_directives->addProp('addColumns', [$tableName, $columnName, $columnDefinition]);
            } elseif (isset($columnDefinition['old_name']) && is_string($columnDefinition['old_name'])) {
                $this->_directives->addProp('changeColumns', [
                    $tableName,
                    $columnDefinition['old_name'],
                    $columnName,
                    $columnDefinition,
                ]);
                $_changeMap[$columnDefinition['old_name']] = $tableName;
            } elseif ($columnDefinition === $a[$columnName]) {
                continue;
            } else {
                $this->_directives->addProp('changeColumns',
                    [$tableName, $columnName, $columnName, $columnDefinition]);
            }
        }
        foreach ($a as $columnName => $columnDefinition) {
            if (!array_key_exists($columnName, $b) && !isset($_changeMap[$columnName])) {
                $this->_directives->addProp('dropColumns', [$tableName, $columnName]);
            }
        }
    }

    /**
     * @param array $a old
     * @param array $b new
     * @param string $tableName
     * @return array
     */
    protected function _intelligentCompareConstraints(array $a, array $b, $tableName)
    {
        foreach ($b as $constraintName => $constraintDefinition) {
            if (!array_key_exists($constraintName, $a)) {
                $this->_directives->addProp('addConstraints',
                    [$tableName, $constraintName, $constraintDefinition]);
            }
        }
        foreach ($a as $constraintName => $constraintDefinition) {
            if (!array_key_exists($constraintName, $b)) {
                $this->_directives->addProp('dropConstraints', [$tableName, $constraintName]);
            }
        }
    }

    /**
     * @param array $a old
     * @param array $b new
     * @param string $tableName
     * @return array
     */
    protected function _intelligentCompareIndices(array $a, array $b, $tableName)
    {
        foreach ($b as $indexName => $indexDefinition) {
            if (!array_key_exists($indexName, $a)) {
                $this->_directives->addProp('addIndices', [$tableName, $indexName, $indexDefinition]);
            }
        }
        foreach ($a as $indexName => $indexDefinition) {
            if (!array_key_exists($indexName, $b)) {
                $this->_directives->addProp('dropIndices', [$tableName, $indexName]);
            }
        }
    }
}
