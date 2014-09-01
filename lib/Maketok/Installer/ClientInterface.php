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
     * @return string
     */
    public function getVersion();

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @return string
     */
    public function getCode();

}
