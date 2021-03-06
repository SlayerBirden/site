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

interface SubjectManagerInterface
{
    /**
     * @param  string|SubjectInterface $subject
     * @param  callable|SubscriberBag  $subscriber
     * @param  int                     $priority
     * @return mixed
     */
    public function attach($subject, $subscriber, $priority);

    /**
     * @param  string|SubjectInterface $subject
     * @param  callable|SubscriberBag|string  $subscriber -> you can use code
     * @return mixed
     */
    public function detach($subject, $subscriber);

    /**
     * @param  string         $subject
     * @param  StateInterface $state
     * @return mixed
     */
    public function notify($subject, StateInterface $state);
}
