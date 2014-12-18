<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl;

use Maketok\App\Helper\UtilityHelperTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class InstallerClient implements ClientInterface
{
    use UtilityHelperTrait;

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
            $this->getLogger()->err($e->getMessage());
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
