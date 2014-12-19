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

interface TableFactoryInterface
{
    /**
     * This is basic factory interface for spawning table mappers
     *
     * @return TableMapper
     */
    public function spawnTable();
}
