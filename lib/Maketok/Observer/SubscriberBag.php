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

/**
 * @codeCoverageIgnore
 */
class SubscriberBag implements \IteratorAggregate
{
    /**
     * @var callable
     */
    public $subscriber;
    /**
     * @var string unique id
     */
    public $code;

    /**
     * @param string $code
     * @param callable $subscriber
     */
    public function __construct($code, callable $subscriber)
    {
        $this->subscriber = $subscriber;
        $this->code = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
}
