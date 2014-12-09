<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;

use Maketok\Util\StreamHandlerInterface;

interface ManagerInterface
{

    /**
     * sets mutex adapter and stream writer as one entity
     *
     * @param StreamHandlerInterface $handler
     * @return $this
     */
    public function setStreamHandler(StreamHandlerInterface $handler);

    /**
     * getter for stream handler
     *
     * @return StreamHandlerInterface
     */
    public function getStreamHandler();

    /**
     * adds client to install queue
     *
     * @param ClientInterface $client
     * @return $this
     */
    public function addClient(ClientInterface $client);

    /**
     * get all clients in queue
     *
     * @return array|\ArrayObject
     */
    public function getClients();

    /**
     * check if has clients
     *
     * @return bool
     */
    public function hasClients();

    /**
     * process all registered clients
     *
     * @return void
     */
    public function process();

    /**
     * get created directives for resource
     *
     * @return DirectivesInterface
     */
    public function getDirectives();

    /**
     * create directives for resource
     *
     * @return void
     */
    public function createDirectives();
}
