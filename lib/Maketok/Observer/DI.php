<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Observer;

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
