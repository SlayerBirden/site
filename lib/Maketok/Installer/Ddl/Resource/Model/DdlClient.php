<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl\Resource\Model;

use Maketok\Model\LazyObjectPropModel;

class DdlClient extends LazyObjectPropModel
{
    // active record properties
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $code;
    /**
     * @var string
     */
    public $version;

    // "private" model properties
    /**
     * @var array
     */
    public $config = [];
    /**
     * @var string[]
     */
    public $dependencies = [];
    /**
     * @var string[]
     */
    public $dependents = [];
    /**
     * @var \DateTime
     */
    public $updated_at;
    /**
     * If client is at it's MAX history version
     * @var bool|int
     */
    public $is_max_version;
    /**
     * MAX history version
     * @var string
     */
    public $max_version;
    /**
     * If client got software update (from code)
     * @var bool|int
     */
    public $got_update;

    /**
     * @param \DateTime $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @return string[]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param string[] $dependencies
     */
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }
}
