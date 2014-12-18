<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Template;

abstract class AbstractEngine implements EngineInterface
{

    /**
     * internal handler for real engine
     * @var object
     */
    protected $_engine;

    /**
     * optional method for configuring different engines
     * @param mixed $options
     * @return mixed
     */
    public function configure($options)
    {
        // some special login?
    }
}
