<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;

interface ClientInterface
{

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

    /**
     * get client version to install
     *
     * @return string
     */
    public function getDataVersion();

    /**
     * get client config to install
     *
     * @param string $version
     * @return array|bool
     */
    public function getDataConfig($version);

    /**
     * get client identifier
     * must be unique
     *
     * @return string
     */
    public function getDataCode();

}
