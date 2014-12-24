<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util\Test;

/**
 * @codeCoverageIgnore
 */
class MuteStub
{
    /**
     * @var mixed
     */
    public $prop;

    /**
     * @param mixed $value
     * @return $this
     */
    public function setProp($value)
    {
        $this->prop = $value;
        return $this;
    }

    /**
     * stub method
     */
    public function doSomething()
    {
    }

    /**
     * stub method
     */
    public function doSomethingElse()
    {
    }

    /**
     * invoker
     */
    public function __invoke()
    {
    }

    /**
     * static method
     */
    public static function StaticMethod()
    {
    }
}
