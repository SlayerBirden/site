<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\ClientInterface as BaseClientInterface;

interface ClientInterface extends BaseClientInterface
{

    const TYPE_INSTALL = 0;
    const TYPE_UPDATE = 1;

    /**
     * register this client for update
     *
     * @param string $version
     * @return void
     */
    public function registerUpdate($version);

    /**
     * register client for install
     *
     * @return void
     */
    public function registerInstall();

    /**
     * get type:
     * 0 - install
     * 1 - update
     *
     * @return string
     */
    public function getType();

    /**
     * get next version (in case of update)
     *
     * @return string
     */
    public function getNextVersion();

    /**
     * claims ownership of some resource;
     * this is 1-many connection,
     * no resource can be owned by 2 clients
     *
     * @param string $resource
     * @return mixed
     */
    public function claimResource($resource);

    /**
     * plural form of claimResource
     *
     * @param array $resources
     * @return mixed
     */
    public function claimResources(array $resources);

    /**
     * registers access to some resource
     * this is many-many connection
     * the difference with resource ownership,
     * is that client that doesn't own resource can't create or delete it
     *
     * @param array $resources
     * @return mixed
     */
    public function registerResourceAccess(array $resources);
}
