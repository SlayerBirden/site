<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Shell\Provider;

interface ProviderInterface
{
    /**
     * @return string[]
     */
    public function getArgs();

    /**
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function getArg($key, $default);
}
