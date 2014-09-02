<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

abstract class AbstractClient implements ClientInterface
{

    /** @var string */
    protected $_type;
    /** @var string */
    public $next_version;

    /**
     * @param string $version
     * @return void
     */
    public function registerUpdate($version)
    {
        $this->next_version = $version;
        $this->_type = self::TYPE_UPDATE;
    }

    /**
     * @return void
     */
    public function registerInstall()
    {
        $this->_type = self::TYPE_INSTALL;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }
}
