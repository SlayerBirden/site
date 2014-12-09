<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\ClientInterface as BaseClientInterface;

interface ClientInterface extends BaseClientInterface
{

    /**
     * client register dependencies (parents)
     * it must register dependencies to change resources that were created by other clients
     *
     * @return array
     */
    public function getDependencies();

    /**
     * get client version to install
     *
     * @return string
     */
    public function getDdlVersion();

    /**
     * get client config to install
     *
     * @param string $version
     * @return array|bool
     */
    public function getDdlConfig($version);

    /**
     * get client identifier
     * must be unique
     *
     * @return string
     */
    public function getDdlCode();
}
