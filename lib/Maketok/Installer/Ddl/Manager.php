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
        $model = $this->getClientModel($client);
        if ($model->config !== FALSE) {
            // only include model if it has config
            $this->_clients[$client->getDdlCode()] = $model;
        }
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
        } catch (Exception $e) {
            // when there's no record for this client yet
            $model = new DdlClient();
            $model->version = $client->getDdlVersion();
            $model->code = $client->getDdlCode();
        } catch (\Exception $e) {
            // when no installer table exists
            $model = new DdlClient();
            $model->version = $client->getDdlVersion();
            $model->code = $client->getDdlCode();
        }
        $model->dependencies = $client->getDependencies();
        $model->config = $client->getDdlConfig($model->version);
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
            $this->_logger->info("Dependency Tree", array(
                'tree' => $this->_reader->getDependencyTree(),
            ));
            // create directives
            $this->createDirectives();
            $this->_logger->info("Directives", array(
                'directives' => $this->_directives->asArray(),
            ));
            // create db procedures
            $this->_resource->createProcedures($this->_directives);

            $this->_logger->info("Procedures", array(
                'procedures' => $this->_resource->getProcedures(),
            ));
            // run
            $this->_resource->runProcedures();
            // @TODO: create backup mechanism
            /** @var DdlClientType $type */
            $type = Site::getServiceContainer()->get('ddl_client_table');
            foreach ($this->_clients as $client) {
                $type->save($client);
            }
            $this->_logger->info("All procedures have been completed.");
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
        $this->_logger->info("Merged Config", array(
            'config' => $config,
        ));
        $this->processValidateMergedConfig($config);
        $this->_logger->info("Processed Merged Config", array(
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
        // make them unique
        foreach ($this->_directives as &$type) {
            $type = $this->_arrayUnique($type);
        }
    }

    /**
     * @param array $a
     * @return array
     */
    private function _arrayUnique(array $a)
    {
        // kind of a hack to make it multi-dimensional
        return array_unique($a, SORT_REGULAR);
    }

    /**
     * first purpose of this is to make sure FK has correspondent index record
     * otherwise create it
     * this is because MySQL automatically creates index record for every FK
     * see more at http://dev.mysql.com/doc/refman/5.6/en/innodb-foreign-key-constraints.html
     *
     * @param array $config
     * @return array
     * @throws Exception
     */
    public function processValidateMergedConfig(array &$config)
    {
        foreach ($config as $tableName => $definition) {
            $needToCreateMap = false;
            $fkMap = array();
            if (isset($definition['constraints'])) {
                foreach ($definition['constraints'] as $name => $constraintDef) {
                    if (isset($constraintDef['type']) && $constraintDef['type'] == 'foreignKey') {
                        // now check if FK has index announced
                        $needToCreateMap = true;
                        $fkMap[$constraintDef['column']] = $name;
                    }
                }
            }
            if ($needToCreateMap) {
                // create index map
                $indexMap = array();
                if (isset($definition['indices'])) {
                    foreach ($definition['indices'] as $name => $indexDef) {
                        if (is_array($indexDef['definition'])) {
                            $col = current($indexDef['definition']);
                        } elseif(is_string($indexDef['definition'])) {
                            $col = $indexDef['definition'];
                        } else {
                            throw new Exception("Unrecognizable index column definition.");
                        }
                        $indexMap[$col] = $name;
                    }
                }
                foreach ($definition['constraints'] as $name => $constraintDef) {
                    if (isset($constraintDef['type']) &&
                        ($constraintDef['type'] == 'uniqueKey' || $constraintDef['type'] == 'primaryKey')) {
                        if (is_array($constraintDef['definition'])) {
                            $col = current($constraintDef['definition']);
                        } elseif(is_string($constraintDef['definition'])) {
                            $col = $constraintDef['definition'];
                        } else {
                            throw new Exception("Unrecognizable index column definition.");
                        }
                        $indexMap[$col] = $name;
                    }
                }
                // check correspondence
                foreach ($fkMap as $column => $name) {
                    if (!isset($indexMap[$column])) {
                        $config[$tableName]['indices'][$name] = [
                            'type' => 'index',
                            'definition' => [$column],
                        ];
                    }
                }
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
            } elseif ($columnDefinition == $a[$columnName]) { // not strict compare because scalar types may differ
                continue;
            } else {
                // now we need to make sure new definitions contain same keys as old ones
                $newDefinition = $columnDefinition;
                $oldDefinition = $a[$columnName];
                foreach ($oldDefinition as $key => $value) {
                    if (!isset($newDefinition[$key])) {
                        unset($oldDefinition[$key]);
                    }
                }
                // not strict compare because scalar types may differ
                if (!($oldDefinition == $newDefinition)) {
                    $this->_directives->addProp('changeColumns',
                        [$tableName, $columnName, $columnName, $newDefinition]);
                }
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
                // now we need to check if in fact the reference column got changed
                foreach ($this->_directives->changeColumns as $columnDirective) {
                    if (isset($constraintDefinition['column']) &&
                        isset($columnDirective[1]) && // key 1 is old name
                        $columnDirective[1] == $constraintDefinition['reference_column']) {
                        $this->_directives->addProp('dropConstraints', [$tableName, $constraintName, $a[$constraintName]['type']]);
                        $this->_directives->addProp('addConstraints',
                            [$tableName, $constraintName, $constraintDefinition]);
                    }
                }
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
