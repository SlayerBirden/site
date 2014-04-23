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
    private $_subscribers = array();

    private $_subjects = array();

    /**
     * @var SubjectManager
     */
    static private $_instance;

    /**
     * @param string $subject
     * @param callable $subscriber
     * @param int $priority
     * @return mixed
     */
    public function attach($subject, $subscriber, $priority)
    {
        if (!$this->getSubject($subject)) {
            $this->addSubject($subject);
        }
        if (!isset($this->_subscribers[$subject])) {
            $this->_subscribers[$subject] = new PriorityQueue();
        }
        $this->_subscribers[$subject]->insert($subscriber, $priority);
    }

    /**
     * @param string $subject
     * @param mixed $subscriber
     * @return mixed
     */
    public function detach($subject, $subscriber)
    {
        if (isset($this->_subscribers[$subject])) {
            $this->_subscribers[$subject]->remove($subscriber);
        }
    }

    /**
     * @param string $subject
     * @param StateInterface $state
     * @return mixed
     */
    public function notify($subject, StateInterface $state)
    {
        if (isset($this->_subscribers[$subject])) {
            $_subject = $this->getSubject($subject);
            /** @var PriorityQueue $_subQueue */
            $_subQueue = $this->_subscribers[$subject];
            $_subQueue->getQueue()->top();
            while ($_subQueue->getQueue()->valid()) {
                if ($_subject->getShouldStopPropagation()) {
                    break;
                }
                // TODO figure out how to inject dependencies
                // maybe we need Service Locator?
                call_user_func($_subQueue->getQueue()->extract(), $state->setSubject($_subject));
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

    /**
     * @return SubjectManager
     */
    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new SubjectManager();
        }
        return self::$_instance;
    }

    protected function __construct()
    {
        // singleton
    }
}