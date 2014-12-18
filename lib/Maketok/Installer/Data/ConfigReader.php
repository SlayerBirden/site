<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Data;

use Maketok\Installer\Data\Resource\Model\DataClient;

/**
 * Class ConfigReader
 * @package Maketok\Installer\Data
 * @codeCoverageIgnore
 */
class ConfigReader implements ConfigReaderInterface
{

    /**
     * @var array
     */
    protected $_config = [];

    /**
     * create config out of clients
     * @param array $clients
     */
    public function createConfig($clients)
    {
        $configsToMerge = [];
        foreach ($clients as $client) {
            /** @var DataClient $client */
            $configsToMerge[] = $client->config;
        }
        $this->mergeConfigs($configsToMerge);
    }

    /**
     * simple data merging
     * @param array $configs
     */
    protected function mergeConfigs(array $configs)
    {
        //@TODO: implement
    }

    /**
     * {@inheritdoc}
     */
    public function getMergedConfig()
    {
        return $this->_config;
    }
}
