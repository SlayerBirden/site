<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;

use Maketok\Installer\Ddl\AbstractClient;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class InstallerClient extends AbstractClient
{

    /**
     * @param string $type
     * @return string
     */
    public function getVersion($type)
    {
        return '0.1.0';
    }

    /**
     * @param string $type
     * @return array
     */
    public function getConfig($type)
    {
        $locator = new FileLocator(__DIR__.'/Resource/config/installer/'.$type);
        $ymlReader = new Yaml();
        return $ymlReader->parse($locator->locate($this->getVersion($type).'.yml'));
    }

    /**
     * @param string $type
     * @return string
     */
    public function getCode($type)
    {
        return 'installer';
    }
}
