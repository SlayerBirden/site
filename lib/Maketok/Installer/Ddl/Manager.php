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

use Maketok\App\Helper\UtilityHelperTrait;
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
use Maketok\Model\TableMapper;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\StreamHandler;
use Maketok\Util\StreamHandlerInterface;

class Manager extends AbstractManager implements ManagerInterface
{
    use UtilityHelperTrait;
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
     * @param TableMapper                 $tableMapper
     */
    public function __construct(ConfigReaderInterface $reader,
                                ResourceInterface $resource,
                                Directives $directives,
                                StreamHandlerInterface $handler = null,
                                TableMapper $tableMapper)
    {
        $this->reader = $reader;
        $this->resource = $resource;
        $this->directives = $directives;
        if ($handler) {
            $this->streamHandler = $handler;
        }
        $this->tableMapper = $tableMapper;
    }

    /**
     * @return StreamHandlerInterface
     */
    public function getStreamHandler()
    {
        if (is_null($this->streamHandler)) {
            $this->streamHandler = new StreamHandler();
        }
        return $this->streamHandler;
    }

    /**
     * @param StreamHandlerInterface $streamHandler
     */
    public function setStreamHandler(StreamHandlerInterface $streamHandler)
    {
        $this->streamHandler = $streamHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function addClient(BaseClientInterface $client)
    {
        if (!($client instanceof ClientInterface)) {
            throw new Exception("Wrong client type.");
        }
        if (is_null($this->clients)) {
            $this->clients = [];
        }
        $model = $this->getClientModel($client);
        if ($model->getConfig() !== false) {
            // only include model if it has config
            $this->clients[$client->getDdlCode()] = $model;
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
        $model->setDependencies($client->getDependencies());
        $model->setConfig($client->getDdlConfig($model->version));

        return $model;
    }

    /**
     * This is where all clients are processed
     * @return void
     */
    public function process()
    {
        // lock process
        if (!$this->getStreamHandler()->lock(AR . '/var/locks/installer.ddl.lock')) {
            $this->getLogger()->info("Installer is locked.");
            return;
        }
        try {
            // build tree
            $this->reader->buildDependencyTree($this->clients);
            $this->getLogger()->info("Dependency Tree", array(
                'tree' => $this->reader->getDependencyTree(),
            ));
            // create directives
            $this->createDirectives();
            $this->getLogger()->info("Directives", array(
                'directives' => $this->directives->asArray(),
            ));
            // create db procedures
            $this->resource->createProcedures($this->directives);

            $this->getLogger()->info("Procedures", array(
                'procedures' => $this->resource->getProcedures(),
            ));
            // run
            $this->resource->runProcedures();
            foreach ($this->clients as $client) {
                try {
                    $this->tableMapper->save($client);
                } catch (ModelException $e) {
                    $this->getLogger()->err($e->__toString());
                }
            }
            $this->getLogger()->info("All procedures have been completed.");
        } catch (\Exception $e) {
            $this->getLogger()->err(sprintf("Exception while running DDL Installer process: %s", $e->__toString()));
        }
        $this->getStreamHandler()->unLock();
    }

    /**
     * @throws \LogicException
     * @return void
     */
    public function createDirectives()
    {
        $config = $this->reader->getMergedConfig();
        $this->getLogger()->info("Merged Config", array(
            'config' => $config,
        ));
        $this->resource->processValidateMergedConfig($config);
        $this->getLogger()->info("Processed Merged Config", array(
            'config' => $config,
        ));
        foreach ($config as $table => $definition) {
            if (!isset($definition['columns']) || !is_array($definition['columns'])) {
                throw new \LogicException(sprintf('Can not have a table `%s` without columns definition.', $table));
            }
            $dbConfig = $this->resource->getTable($table);
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
}
