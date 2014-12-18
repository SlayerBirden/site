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


use Maketok\Util\Exception\DirectoryException;

interface DirectoryHandlerInterface
{
    /**
     * @param string $path
     * @return bool
     */
    public function rm($path);

    /**
     * @param string $path
     * @param int $permissions
     * @param bool $recursive
     * @return bool
     */
    public function mkdir($path, $permissions = 0755, $recursive = true);


    /**
     * @param null|string $path
     * @param bool $namesOnly
     * @return array
     * @throws DirectoryException
     */
    public function ls($path, $namesOnly = true);
}
