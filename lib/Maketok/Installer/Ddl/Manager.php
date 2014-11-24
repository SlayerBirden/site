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
use Maketok\Installer\Ddl\Resource\Model\DdlClient;
use Maketok\Installer\Ddl\Resource\Model\DdlClientType;
use Maketok\Installer\Exception;
use Maketok\Installer\ManagerInterface;
use Maketok\Installer\ClientInterface as BaseClientInterface;
use Maketok\Installer\Ddl\ClientInterface as DdlClientInterface;
use Maketok\Util\StreamHandlerInterface;
use Monolog\Logger;

class Manager extends AbstractManager implements ManagerInterface
{
    /**
     * @var Logger
     */
    private $_logger;

    /**
     * Constructor
     * @param ConfigReaderInterface $reader
     * @param ResourceInterface $resource
     * @param Directives $directives
     * @param StreamHandlerInterface|null $handler
     * @param Logger $logger
     */
    public function __construct(ConfigReaderInterface $reader,
                                ResourceInterface $resource,
                                Directives $directives,
                                StreamHandlerInterface $handler = null,
                                Logger $logger)
    {
        $this->_reader = $reader;
        $this->_streamHandler = $handler;
        $this->_directives = $directives;
        if ($handler) {
            $this->_resource = $resource;
        }
        $this->_logger = $logger;
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
        $this->_logger->debug(sprintf("Client added: %s", $client->getDdlCode()));
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
            $this->_logger->debug("Dependency Tree: %s", array(
                'tree' => $this->_reader->getDependencyTree(),
            ));
            // create directives
            $this->createDirectives();
            $this->_logger->debug("Directives: %s", array(
                'directives' => $this->_directives->asArray(),
            ));
            // create db procedures
            $this->_resource->createProcedures($this->_directives);

            $this->_logger->debug("Procedures: %s", array(
                'procedures' => $this->_resource->getProcedures(),
            ));
            // run
            $this->_resource->runProcedures();
            $this->_logger->debug("All procedures have been completed.");
        } catch (\Exception $e) {
            $this->_logger->err(sprintf("Exception while running DDL Installer process: %s", $e->__toString()));
        }
        $this->_streamHandler->unLock();
    }

    /**
     * @throws \LogicException
     * @return void
     */
    public function createDirectives()
    {
        $config = $this->_reader->getMergedConfig();
        $this->_logger->debug("Merged Config: %s", array(
            'config' => $config,
        ));
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
            } elseif ((isset($constraintDefinition['definition']) &&
                $constraintDefinition['definition'] === $a[$constraintName]['definition']) || (
                isset($constraintDefinition['column']) &&
                $constraintDefinition['column'] == $a[$constraintName]['column'] &&
                isset($constraintDefinition['reference_table']) &&
                $constraintDefinition['reference_table'] == $a[$constraintName]['reference_table'] &&
                isset($constraintDefinition['reference_column']) &&
                $constraintDefinition['reference_column'] == $a[$constraintName]['reference_column'] &&
                (!isset($constraintDefinition['on_delete']) ||
                    $constraintDefinition['on_delete'] == $a[$constraintName]['on_delete']) &&
                (!isset($constraintDefinition['on_update']) ||
                    $constraintDefinition['on_update'] == $a[$constraintName]['on_update'])
                )) {
                continue;
            } else {
                $this->_directives->addProp('dropConstraints', [$tableName, $constraintName, $a[$constraintName]['type']]);
                $this->_directives->addProp('addConstraints',
                    [$tableName, $constraintName, $constraintDefinition]);
            }
        }
        foreach ($a as $constraintName => $constraintDefinition) {
            if (!array_key_exists($constraintName, $b)) {
                $this->_directives->addProp('dropConstraints', [$tableName, $constraintName, $constraintDefinition['type']]);
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
            } elseif ($indexDefinition['definition'] === $a[$indexName]['definition']) {
                continue;
            } else {
                $this->_directives->addProp('dropIndices', [$tableName, $indexDefinition]);
                $this->_directives->addProp('addIndices',
                    [$tableName, $indexName, $indexDefinition]);
            }
        }
        foreach ($a as $indexName => $indexDefinition) {
            if (!array_key_exists($indexName, $b)) {
                $this->_directives->addProp('dropIndices', [$tableName, $indexName]);
            }
        }
    }
}
