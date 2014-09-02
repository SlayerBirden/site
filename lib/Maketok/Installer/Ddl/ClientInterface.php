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
     * @param string $version
     * @return void
     */
    public function registerUpdate($version);

    /**
     * @return void
     */
    public function registerInstall();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getNextVersion();
}
