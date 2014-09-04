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
     * @param string $type
     * @return string
     */
    public function getVersion($type);

    /**
     * @param string $type
     * @return array|bool
     */
    public function getConfig($type);

    /**
     * @param string $type
     * @return string
     */
    public function getCode($type);

}
