<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Data;

use Maketok\Installer\Data\Resource\Model\DataClient;

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
            $configsToMerge = $client->config;
        }
        $this->mergeConfigs($configsToMerge);
    }

    /**
     * simple data merging
     * @param array $configs
     */
    protected function mergeConfigs(array $configs)
    {
        foreach ($configs as $table => $data) {
            $_config[$table][] = $data;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMergedConfig()
    {
        return $this->_config;
    }
}
