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
    /** @var \Maketok\Util\Zend\Db\Sql\Sql  */
    protected $_sql;
    /** @var \Maketok\Util\AbstractTableMapper  */
    protected $_tableMapper;
    /** @var string */
    protected $_type;


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
        $this->_clients[$client->getCode($this->_type)] = $client;
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

    /**
     * the recursive compare function
     * should compare versions
     * @param $a
     * @param $b
     * @return int
     */
    public function natRecursiveCompare($a, $b)
    {
        $aA = explode('.', $a);
        $aB = explode('.', $b);
        if (count($aA) > count($aB)) {
            for ($i = count($aB); $i < count($aA); $i++){
                $aB[] = 0;
            }
        } elseif(count($aB) > count($aA)) {
            for ($i = count($aA); $i < count($aB); $i++){
                $aA[] = 0;
            }
        }
        // cast all versions to int
        foreach ($aA as &$v) {$v = (int) $v;}
        foreach ($aB as &$v) {$v = (int) $v;}
        for ($i = 0; $i < count($aA); $i++) {
            if ($aA[$i] > $aB[$i]) {
                return 1;
            } elseif ($aB[$i] > $aA[$i]) {
                return -1;
            } elseif ($aA[$i] === $aB[$i]) {
                continue;
            }
        }
        // versions are identical
        return 0;
    }
}
