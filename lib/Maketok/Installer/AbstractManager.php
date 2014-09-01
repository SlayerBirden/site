<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;

use Maketok\Util\StreamHandlerInterface;
use Zend\Db\Adapter\Adapter;

abstract class AbstractManager implements ManagerInterface
{

    /** @var array */
    protected $_clients;
    /** @var Adapter */
    protected $_adapter;
    /** @var ConfigReaderInterface */
    protected $_reader;
    /** @var StreamHandlerInterface */
    protected $_streamHandler;
    /** @var array */
    protected $_messages;


    /**
     * Constructor
     * @param Adapter $adapter
     * @param ConfigReaderInterface $reader
     * @param StreamHandlerInterface $handler
     */
    public function __construct(Adapter $adapter,
                                ConfigReaderInterface $reader,
                                StreamHandlerInterface $handler = null)
    {
        $this->_adapter = $adapter;
        $this->_reader = $reader;
        if (!is_null($handler)) {
            $this->_streamHandler = $handler;
        }
    }

    /**
     * @param StreamHandlerInterface $handler
     * @return $this
     */
    public function setStreamHandler(StreamHandlerInterface $handler)
    {
        $this->_streamHandler = $handler;
        return $this;
    }

    /**
     * @return StreamHandlerInterface
     */
    public function getStreamHandler()
    {
        return $this->_streamHandler;
    }

    /**
     * @param ClientInterface $client
     * @return $this
     */
    public function addClient(ClientInterface $client)
    {
        if (is_null($this->_clients)) {
            $this->_clients = [];
        }
        $this->_clients[$client->getCode()] = $client;
    }

    /**
     * @return array|\ArrayObject
     */
    public function getClients()
    {
        return $this->_clients;
    }

    /**
     * @return bool
     */
    public function hasClients()
    {
        return count($this->_clients) > 0;
    }
}
