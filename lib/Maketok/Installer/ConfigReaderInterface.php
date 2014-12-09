<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;

interface ConfigReaderInterface
{

    /**
     * return merged config
     *
     * @return array
     */
    public function getMergedConfig();
}
