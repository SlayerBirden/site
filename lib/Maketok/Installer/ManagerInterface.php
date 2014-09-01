<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;

use Maketok\Util\StreamHandlerInterface;

interface ManagerInterface
{

    /**
     * @param StreamHandlerInterface $handler
     * @return $this
     */
    public function setStreamHandler(StreamHandlerInterface $handler);

    /**
     * @return StreamHandlerInterface
     */
    public function getStreamHandler();

    /**
     * @param ClientInterface $client
     * @return $this
     */
    public function addClient(ClientInterface $client);

    /**
     * @return array|\ArrayObject
     */
    public function getClients();

    /**
     * @return bool
     */
    public function hasClients();

    /**
     * This is where all clients are processed
     * @return void
     */
    public function process();
}
