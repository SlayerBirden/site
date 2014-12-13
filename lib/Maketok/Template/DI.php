<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template;

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
        $loader->load(__DIR__.'/Resource/config/di/parameters.yml');
    }
}
