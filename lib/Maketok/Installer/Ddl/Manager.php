<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\AbstractManager;
use Maketok\Installer\Exception;
use Maketok\Installer\ManagerInterface;
use Maketok\Installer\ClientInterface as BaseClientInterface;

class Manager extends AbstractManager implements ManagerInterface
{

    /** @var array */
    protected $_directives;

    /**
     * {@inherited}
     */
    public function addClient(BaseClientInterface $client)
    {
        if (!($client instanceof ClientInterface)) {
            throw new Exception("Wrong client type.");
        }
        parent::addClient($client);
    }

    /**
     * @param ClientInterface $client
     * @return bool
     */
    public function validateClient(ClientInterface $client)
    {

    }

    /**
     * This is where all clients are processed
     * @return void
     */
    public function process()
    {
        $this->_streamHandler->lock();
        foreach ($this->_clients as $client) {
            if (!$this->validateClient($client)) {
                continue;
            }
            /** @var ClientInterface $client */
            $this->_reader->processConfig($client->getConfig());
            $this->_reader->validateDirectives();
            $this->mergeDirectives($this->_reader->getDirectives());
        }
        $this->applyDirectives();
    }

    /**
     * @param array $newDirectives
     * @return array
     */
    public function mergeDirectives(array $newDirectives)
    {
        // @TODO add logic
    }

    /**
     * @return void
     */
    public function applyDirectives()
    {
        // @TODO add logic
    }
}
