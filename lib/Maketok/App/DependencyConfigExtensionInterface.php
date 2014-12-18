<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\App;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

interface DependencyConfigExtensionInterface
{

    /**
     * Loads the config right into DI Container
     * not waiting for compile
     *
     * @param  YamlFileLoader $loader
     * @return mixed
     */
    public function loadConfig(YamlFileLoader $loader);
}
