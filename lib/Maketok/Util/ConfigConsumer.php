<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util;

interface ConfigConsumer
{

    /**
     * initialize configuration from resources
     * @return void
     * @codeCoverageIgnore
     */
    public function initConfig();

    /**
     * process config for initConfig
     * @param array $config
     * @return void
     */
    public function parseConfig(array $config);
}
