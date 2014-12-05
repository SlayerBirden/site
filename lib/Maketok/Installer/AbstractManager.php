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
    /** @var DirectivesInterface */
    protected $_directives;
    /**
     * @var ResourceInterface
     */
    protected $_resource;

    /**
     * Constructor
     * @param ConfigReaderInterface $reader
     * @param ResourceInterface $resource
     * @param DirectivesInterface $directives
     * @param StreamHandlerInterface|null $handler
     */
    public function __construct(ConfigReaderInterface $reader,
                                ResourceInterface $resource,
                                DirectivesInterface $directives,
                                StreamHandlerInterface $handler = null)
    {
        $this->_reader = $reader;
        $this->_streamHandler = $handler;
        $this->_directives = $directives;
        if ($handler) {
            $this->_resource = $resource;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStreamHandler(StreamHandlerInterface $handler)
    {
        $this->_streamHandler = $handler;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStreamHandler()
    {
        return $this->_streamHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getClients()
    {
        return $this->_clients;
    }

    /**
     * {@inheritdoc}
     */
    public function hasClients()
    {
        return count($this->_clients) > 0;
    }

    /**
     * the recursive compare function
     * should compare versions
     * for strings only!
     *
     * @param string $a
     * @param string $b
     * @throws \InvalidArgumentException
     * @return int
     */
    public function natRecursiveCompare($a, $b)
    {
        if (!is_string($a) || !is_string($b)) {
            throw new \InvalidArgumentException("Compared arguments must be strings.");
        }
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
        // identical
        return 0;
    }

    /**
     * @return DirectivesInterface
     */
    public function getDirectives()
    {
        return $this->_directives;
    }
}
