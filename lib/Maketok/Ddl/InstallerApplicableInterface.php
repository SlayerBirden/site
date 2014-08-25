<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Ddl;

interface InstallerApplicableInterface
{
    /**
     * @return array
     */
    public static function getDdlConfig();

    /**
     * @return string
     */
    public static function getDdlConfigVersion();

    /**
     * @return string
     */
    public static function getDdlConfigName();
}
