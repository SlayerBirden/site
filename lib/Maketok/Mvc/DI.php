<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc;

use Maketok\App\ContainerFactory;
use Maketok\App\DependencyConfigExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @codeCoverageIgnore
 */
class DI implements DependencyConfigExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadConfig(YamlFileLoader $loader)
    {
        $loader->load(__DIR__.'/Resource/config/di/services.yml');
        if (($env = ContainerFactory::getEnv()) && !empty($env)) {
            try {
                $loader->load(__DIR__.'/Resource/config/di/' . $env . '.services.yml');
            } catch (\Exception $e) {
            }
        }
    }
}
