<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    /** @var string */
    protected $_type;
    /** @var DirectivesInterface */
    protected $directives;
    /**
     * @var ResourceInterface
     */
    protected $_resource;

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
     * @return DirectivesInterface
     */
    public function getDirectives()
    {
        return $this->directives;
    }
}
