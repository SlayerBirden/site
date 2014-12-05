<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Resource\Model;

class DdlClient
{

    protected $_origin = [];

    /** @var int */
    public $id;
    /** @var string */
    public $code;
    /** @var string */
    public $version;
    /** @var array */
    public $config;
    /** @var array */
    public $dependencies;

    /**
     * @param array $data
     * @return $this
     */
    public function setOrigin(array $data)
    {
        $this->_origin = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrigin()
    {
        return $this->_origin;
    }
}
