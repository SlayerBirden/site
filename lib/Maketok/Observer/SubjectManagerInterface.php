<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Observer;

interface SubjectManagerInterface
{
    /**
     * @param string $subject
     * @param callable $subscriber
     * @param int $priority
     * @return mixed
     */
    public function attach($subject, $subscriber, $priority);

    /**
     * @param string $subject
     * @param callable $subscriber
     * @return mixed
     */
    public function detach($subject, $subscriber);

    /**
     * @param string $subject
     * @param State $state
     * @return mixed
     */
    public function notify($subject, State $state);
}