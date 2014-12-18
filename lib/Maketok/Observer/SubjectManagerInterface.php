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
     * @param  string   $subject
     * @param  callable $subscriber
     * @param  int      $priority
     * @return mixed
     */
    public function attach($subject, $subscriber, $priority);

    /**
     * @param  string   $subject
     * @param  callable $subscriber
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
