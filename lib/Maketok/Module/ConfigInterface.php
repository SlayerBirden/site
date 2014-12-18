<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Module;


interface ConfigInterface
{

    /**
     * @return void
     */
    public function initRoutes();

    /**
     * @return string
     */
    public function getVersion();

    /**
     * @return void
     */
    public function initListeners();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return string
     */
    public function getCode();

    /**
     * magic method for returning string representation of the the config class
     * @return string
     */
    public function __toString();
}
