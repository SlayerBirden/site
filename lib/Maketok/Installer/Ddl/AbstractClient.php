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

    /** @var array */
    protected $_dependencies;

    /**
     * {@inheritdoc}
     */
    public function registerDependencies(array $dependencies)
    {
        $this->_dependencies = array_replace($this->_dependencies, $dependencies);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataConfig($version)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDataVersion()
    {
        return '0';
    }

    /**
     * {@inheritdoc}
     */
    public function getDataCode()
    {
        return '';
    }
}
