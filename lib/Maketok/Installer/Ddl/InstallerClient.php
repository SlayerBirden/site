<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\App\Site;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class InstallerClient implements ClientInterface
{

    /**
     * {@inheritdoc}
     */
    public function getDdlVersion()
    {
        return '0.1.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlConfig($version)
    {
        $locator = new FileLocator(__DIR__.'/Resource/config/installer/ddl');
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

    /**
     * client register dependencies (parents)
     * it must register dependencies to change resources that were created by other clients
     *
     * @return array
     */
    public function getDependencies()
    {
        return [];
    }
}
