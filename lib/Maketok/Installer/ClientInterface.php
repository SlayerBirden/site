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
     * @param string $type
     * @return string
     */
    public function getVersion($type);

    /**
     * get client config to install
     *
     * @param string $type
     * @return array|bool
     */
    public function getConfig($type);

    /**
     * get client identifier
     * must be unique
     *
     * @param string $type
     * @return string
     */
    public function getCode($type);

}
