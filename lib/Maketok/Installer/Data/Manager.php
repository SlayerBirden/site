<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Data;

use Maketok\Installer\AbstractManager;
use Maketok\Installer\Exception;
use Maketok\Installer\ManagerInterface;
use Maketok\Installer\ClientInterface as BaseClientInterface;
use Maketok\Util\StreamHandlerInterface;

class Manager extends AbstractManager implements ManagerInterface
{

    /**
     * Constructor
     * @param ConfigReaderInterface $reader
     * @param ResourceInterface $resource
     * @param Directives $directives
     * @param StreamHandlerInterface|null $handler
     */
    public function __construct(ConfigReaderInterface $reader,
                                ResourceInterface $resource,
                                Directives $directives,
                                StreamHandlerInterface $handler = null)
    {
        $this->_reader = $reader;
        $this->_streamHandler = $handler;
        $this->directives = $directives;
        if ($handler) {
            $this->_resource = $resource;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        // TODO: Implement process() method.
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
        $this->_clients[$client->getDataCode()] = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectives()
    {
        // TODO: Implement createDirectives() method.
    }
}
