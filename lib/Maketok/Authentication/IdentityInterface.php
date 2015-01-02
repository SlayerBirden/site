<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Authentication;

interface IdentityInterface
{
    /**
     * @return int[]
     */
    public function getRoles();
    /**
     * @return string
     */
    public function getUsername();
}
