<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer;

interface ConfigReaderInterface
{

    /**
     * return merged config
     *
     * @return array
     */
    public function getMergedConfig();
}
