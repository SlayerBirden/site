<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Observer;

interface SubjectInterface
{
    /**
     * @return bool
     */
    public function getShouldStopPropagation();

    /**
     * @param  bool | int $flag
     * @return self
     */
    public function setShouldStopPropagation($flag);

    /**
     * @return string
     */
    public function __toString();
}
