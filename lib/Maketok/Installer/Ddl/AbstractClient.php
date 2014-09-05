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
    /** @var array */
    protected $_ownedResources;
    /** @var array */
    protected $_accessResources;

    /**
     * {@inheritdoc}
     */
    public function registerUpdate($version)
    {
        $this->next_version = $version;
        $this->_type = self::TYPE_UPDATE;
    }

    /**
     * {@inheritdoc}
     */
    public function registerInstall()
    {
        $this->_type = self::TYPE_INSTALL;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * {@inheritdoc}
     */
    public function getNextVersion()
    {
        return $this->next_version;
    }

    /**
     * {@inheritdoc}
     */
    public function registerResourceAccess(array $resources)
    {

        $this->_accessResources = array_merge($this->_accessResources, $resources);
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function claimResource($resource)
    {
        if (!is_string($resource)) {
            throw new \InvalidArgumentException("Resource identifier must be a string.");
        }
        $this->_ownedResources[] = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function claimResources(array $resources)
    {
        foreach ($resources as $resource) {
            $this->claimResource((string) $resource);
        }
    }
}
