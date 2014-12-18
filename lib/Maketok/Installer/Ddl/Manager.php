<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\AbstractManager;
use Maketok\Installer\Ddl\Manager\Columns;
use Maketok\Installer\Ddl\Manager\Constraints;
use Maketok\Installer\Ddl\Manager\Indices;
use Maketok\Installer\Ddl\Resource\Model\DdlClient;
use Maketok\Installer\Ddl\Resource\Model\DdlClientType;
use Maketok\Installer\Exception;
use Maketok\Installer\ManagerInterface;
use Maketok\Installer\ClientInterface as BaseClientInterface;
use Maketok\Installer\Ddl\ClientInterface as DdlClientInterface;
use Maketok\Util\ArrayValueTrait;
use Maketok\Model\TableMapper;
use Maketok\Util\StreamHandlerInterface;
use Monolog\Logger;

class Manager extends AbstractManager implements ManagerInterface
{
    use ArrayValueTrait;
    /**
     * @var Logger
     */
    private $_logger;
    /**
     * @var DdlClientType
     */
    private $tableMapper;

    /**
     * Constructor
     * @param ConfigReaderInterface       $reader
     * @param ResourceInterface           $resource
     * @param Directives                  $directives
     * @param StreamHandlerInterface|null $handler
     * @param Logger                      $logger
     * @param TableMapper                 $tableMapper
     */
    public function __construct(ConfigReaderInterface $reader,
                                ResourceInterface $resource,
                                Directives $directives,
                                StreamHandlerInterface $handler = null,
                                Logger $logger,
                                TableMapper $tableMapper)
    {
        $this->_reader = $reader;
        $this->_streamHandler = $handler;
        $this->directives = $directives;
        if ($handler) {
            $this->_resource = $resource;
        }
        $this->_logger = $logger;
        $this->tableMapper = $tableMapper;
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
     * @param  DdlClientInterface $client
     * @return DdlClient
     */
    public function getClientModel(DdlClientInterface $client)
    {
        try {
            $model = $this->tableMapper->getClientByCode($client->getDdlCode());
        } catch (Exception $e) {
            // when there's no record for this client yet
            $model = new DdlClient();
            $model->code = $client->getDdlCode();
        } catch (\Exception $e) {
            // when no installer table exists
            $model = new DdlClient();
            $model->code = $client->getDdlCode();
        }
        $model->version = $client->getDdlVersion();
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
                'directives' => $this->directives->asArray(),
            ));
            // create db procedures
            $this->_resource->createProcedures($this->directives);

            $this->_logger->info("Procedures", array(
                'procedures' => $this->_resource->getProcedures(),
            ));
            // run
            $this->_resource->runProcedures();
            // @TODO: create backup mechanism
            foreach ($this->_clients as $client) {
                $this->tableMapper->save($client);
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
                throw new \LogicException(sprintf('Can not have a table `%s` without columns definition.', $table));
            }
            $dbConfig = $this->_resource->getTable($table);
            // compare def with db
            if (empty($dbConfig)) {
                // add table
                $this->directives->addProp('addTables', [$table, $definition]);
            } else {
                $colCompare = new Columns();
                $colCompare->intlCompare($dbConfig['columns'], $definition['columns'], $table, $this->directives);
                $_oldConstraints = $this->getIfExists('constraints', $dbConfig, array());
                $_newConstraints = $this->getIfExists('constraints', $definition, array());
                $conCompare = new Constraints();
                $conCompare->intlCompare($_oldConstraints, $_newConstraints, $table, $this->directives);
                $_oldIndices = $this->getIfExists('indices', $dbConfig, array());
                $_newIndices = $this->getIfExists('indices', $definition, array());
                $idxCompare = new Indices();
                $idxCompare->intlCompare($_oldIndices, $_newIndices, $table, $this->directives);
            }
        }
        $this->directives->unique();
    }

    /**
     * first purpose of this is to make sure FK has correspondent index record
     * otherwise create it
     * this is because MySQL automatically creates index record for every FK
     * see more at http://dev.mysql.com/doc/refman/5.6/en/innodb-foreign-key-constraints.html
     *
     * @param  array     $config
     * @return void
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
                        } elseif (is_string($indexDef['definition'])) {
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
                        } elseif (is_string($constraintDef['definition'])) {
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
}
