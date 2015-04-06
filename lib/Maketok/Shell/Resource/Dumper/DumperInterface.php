<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Shell\Resource\Dumper;

interface DumperInterface
{
    /**
     * @param mixed $input
     * @param int $level
     * @param int $indent
     * @return mixed
     */
    public function write($input, $level = 3, $indent = 0);

    /**
     * @return string
     */
    public function getExtension();
}
