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

use Symfony\Component\Config\Loader\FileLoader;

interface ConfigInterface
{

    /**
     * Loads the config right into appropriate container
     *
     * @param  FileLoader $loader
     * @return mixed
     */
    public function loadConfig(FileLoader $loader);
}
