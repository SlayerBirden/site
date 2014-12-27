<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Navigation\Dumper;

interface DumperInterface
{
    /**
     * dump navigation to a file
     * @param  string $path
     * @return mixed
     */
    public function write($path);

    /**
     * @return string
     */
    public function getFileExtension();
}
