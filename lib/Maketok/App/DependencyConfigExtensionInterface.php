<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

interface DependencyConfigExtensionInterface
{

    /**
     * Loads the config right into DI Container
     * not waiting for compile
     *
     * @param YamlFileLoader $loader
     * @return mixed
     */
    public function loadConfig(YamlFileLoader $loader);
}
