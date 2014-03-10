<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Observer;

use Maketok\Util\PriorityQueue;

class SubjectManager implements SubjectManagerInterface
{
    /**
     * array of PriorityQueue objects
     * @var array
     */
    private $_mapper = array();

    private $_subjects = array();

    /**
     * @param string $subject
     * @param SubscriberInterface $subscriber
     * @param int $priority
     * @return mixed
     */
    public function attach($subject, SubscriberInterface $subscriber, $priority)
    {
        if (!$this->getSubject($subject)) {
            $this->addSubject($subject);
        }
        if (!isset($this->_mapper[$subject])) {
            $this->_mapper[$subject] = new PriorityQueue();
        }
        $this->_mapper[$subject]->insert($subscriber, $priority);
    }

    /**
     * @param string $subject
     * @param SubscriberInterface $subscriber
     * @return mixed
     */
    public function detach($subject, SubscriberInterface $subscriber)
    {
        if (isset($this->_mapper[$subject])) {
            $this->_mapper[$subject]->remove($subscriber);
        }
    }

    /**
     * @param string $subject
     * @param State $state
     * @return mixed
     */
    public function notify($subject, State $state)
    {
        if (isset($this->_mapper[$subject])) {
            foreach ($this->_mapper[$subject] as $subscriber) {
                $subscriber->update($state->setSubject($this->getSubject($subject)));
            }
        }
    }

    /**
     * @param string $subject
     * @return Subject|bool
     */
    public function getSubject($subject)
    {
        if (isset($this->_subjects[$subject])) {
            return $this->_subjects[$subject];
        }
        return false;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function addSubject($subject)
    {
        $this->_subjects[$subject] = new Subject($subject);
        return $this;
    }
}