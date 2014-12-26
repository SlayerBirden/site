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

class MuteStub
{
    /**
     * @var mixed
     */
    public $prop;

    /**
     * @var array
     */
    protected $params;

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
     * constructor
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return null;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $key = strtolower(substr($name, 3, strlen($name) - 3));
            if (isset($this->params[$key])) {
                return $this->params[$key];
            }
        }
        throw new \InvalidArgumentException(sprintf("Method %s doesn't exist.", $name));
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
