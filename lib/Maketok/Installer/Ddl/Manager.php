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
use Maketok\Util\StreamHandlerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Manager extends AbstractManager implements ManagerInterface
{

    /** @var Directives */
    private $_directives;
    /**
     * @var ResourceInterface
     */
    protected $_resource;

    /** @var array */
    protected  $_availableConstraintTypes = ['primaryKey', 'uniqueKey', 'foreignKey'];


    /**
     * Constructor
     * @param Adapter $adapter
     * @param \Maketok\Util\Zend\Db\Sql\Sql|\Zend\Db\Sql\Sql $sql
     * @param ConfigReaderInterface $reader
     * @param ResourceInterface $resource
     * @param StreamHandlerInterface $handler
     */
    public function __construct(Adapter $adapter,
                                Sql $sql,
                                ConfigReaderInterface $reader,
                                ResourceInterface $resource,
                                StreamHandlerInterface $handler = null)
    {
        $this->_adapter = $adapter;
        $this->_reader = $reader;
        if (!is_null($handler)) {
            $this->_streamHandler = $handler;
        }
        $this->_sql = $sql;
        $this->_type = 'ddl';
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
        parent::addClient($client);
    }

    /**
     * This is where all clients are processed
     * @return void
     */
    public function process()
    {

    }

    /**
     * @throws \LogicException
     * @return void
     */
    public function createDirectives()
    {
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
                $this->_directives['addTables'] = [$table, $definition];
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
        foreach ($b as $columnName => $columnDefinition) {
            if (!array_key_exists($columnName, $a) && !isset($columnDefinition['old_name'])) {
                $this->_directives->addColumns[] = [$tableName, $columnName, $columnDefinition];
            } elseif (isset($columnDefinition['old_name']) && is_string($columnDefinition['old_name'])) {
                $this->_directives->changeColumns[] = [
                    $tableName,
                    $columnDefinition['old_name'],
                    $columnName,
                    $columnDefinition,
                ];
            } elseif ($columnDefinition === $a[$columnName]) {
                continue;
            } else {
                $this->_directives->changeColumns[] = [$tableName, $columnName,  $columnName, $columnDefinition];
            }
        }
        foreach ($a as $columnName => $columnDefinition) {
            if (!array_key_exists($columnName, $b)) {
                $this->_directives->dropColumns[] = [$tableName, $columnName];
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
                $this->_directives->addConstraints[] = [$tableName, $constraintName, $constraintDefinition];
            }
        }
        foreach ($a as $constraintName => $constraintDefinition) {
            if (!array_key_exists($constraintName, $b)) {
                $this->_directives->dropConstraints[] = [$tableName, $constraintName];
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
                $this->_directives->addIndices[] = [$tableName, $indexName, $indexDefinition];
            }
        }
        foreach ($a as $indexName => $indexDefinition) {
            if (!array_key_exists($indexName, $b)) {
                $this->_directives->dropIndices[] = [$tableName, $indexName];
            }
        }
    }
}
