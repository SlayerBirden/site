<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Observer;

class SubjectManager implements SubjectManagerInterface
{
    private $_mapper;

    public function __construct()
    {
        $_mapper = new \SplPriorityQueue();
    }

    /**
     * @param string $subject
     * @param SubscriberInterface $subscriber
     * @param int $priority
     * @return mixed
     */
    public function attach($subject, SubscriberInterface $subscriber, $priority)
    {
        // TODO: Implement attach() method.
    }

    /**
     * @param string $subject
     * @param SubscriberInterface $subscriber
     * @return mixed
     */
    public function detach($subject, SubscriberInterface $subscriber)
    {
        // TODO: Implement detach() method.
    }

    /**
     * @param string $subject
     * @param object $state
     * @return mixed
     */
    public function notify($subject, $state)
    {
        // TODO: Implement notify() method.
    }
}