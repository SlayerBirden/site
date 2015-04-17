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

interface ManagerInterface
{
    /**
     * adds client to install queue
     *
     * @param  ClientInterface $client
     * @return $this
     */
    public function addClient(ClientInterface $client);

    /**
     * get all clients in queue
     *
     * @return ClientInterface[]
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
     * Returns number of operations performed
     *
     * @return int
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
