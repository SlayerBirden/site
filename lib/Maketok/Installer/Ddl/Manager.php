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

    /** @var array */
    protected $_directives;
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
     * @return bool
     */
    public function validateConfig()
    {
        // @TODO add logic
    }

    /**
     * @return void
     */
    public function createDirectives()
    {
        // @TODO add logic
    }
}
