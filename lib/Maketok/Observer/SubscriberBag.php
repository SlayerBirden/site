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
     * @var SubjectInterface
     */
    public $subject;

    /**
     * @param callable         $subscriber
     * @param SubjectInterface $subject
     */
    public function __construct($subscriber, SubjectInterface $subject)
    {
        $this->subscriber = $subscriber;
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
}
