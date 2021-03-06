<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Shell\Worker;

interface WorkerInterface
{
    /**
     * do work
     */
    public function run();

    /**
     * @return string representation
     */
    public function __toString();
}
