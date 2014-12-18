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

interface ConfigReaderInterface
{

    /**
     * @param string $path config path
     * @return mixed|array
     */
    public function source($path);
}
