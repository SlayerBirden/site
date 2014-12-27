<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Model;

interface LazyModelInterface
{
    /**
     * define strategy of getting/setting data
     * for each concrete implementation
     * this is combined setter/getter
     * @param  array|null $data
     * @return mixed
     */
    public function processOrigin(array $data = null);
}
