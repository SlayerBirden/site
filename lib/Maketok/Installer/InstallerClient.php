<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;

use Maketok\App\Site;
use Maketok\Installer\Ddl\AbstractClient;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class InstallerClient extends AbstractClient
{

    /**
     * {@inheritdoc}
     */
    public function getDdlVersion()
    {
        return '0.1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlConfig($version)
    {
        $locator = new FileLocator(__DIR__.'/Resource/config/installer/'.$type);
        $ymlReader = new Yaml();
        try {
            $file = $locator->locate($version.'.yml');
        } catch (\InvalidArgumentException $e) {
            Site::getServiceContainer()->get('logger')->err($e->getMessage());
            return false;
        }
        return $ymlReader->parse($file);
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlCode()
    {
        return 'installer';
    }
}
