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

use Maketok\Util\PriorityQueue;

class SubjectManager implements SubjectManagerInterface
{
    /**
     * array of PriorityQueue objects
     * @var PriorityQueue[]
     */
    private $subscribers = array();

    /**
     * @var SubjectInterface[]
     */
    private $subjects = array();

    /**
     * @var SubjectManager
     */
    static private $instance;

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
        if (!isset($this->subscribers[$subject])) {
            $this->subscribers[$subject] = new PriorityQueue();
        }
        $this->subscribers[$subject]->insert($subscriber, $priority);
    }

    /**
     * @param string $subject
     * @param mixed $subscriber
     * @return mixed
     */
    public function detach($subject, $subscriber)
    {
        if (isset($this->subscribers[$subject])) {
            $this->subscribers[$subject]->remove($subscriber);
        }
    }

    /**
     * @param string $subject
     * @param StateInterface $state
     * @return mixed
     */
    public function notify($subject, StateInterface $state)
    {
        if (isset($this->subscribers[$subject])) {
            $_subject = $this->getSubject($subject);
            /** @var PriorityQueue $_subQueue */
            $_subQueue = $this->subscribers[$subject];
            $_subQueue->getQueue()->top();
            while ($_subQueue->getQueue()->valid()) {
                if ($_subject->getShouldStopPropagation()) {
                    break;
                }
                call_user_func($_subQueue->getQueue()->extract(), $state->setSubject($_subject));
            }
        }
    }

    /**
     * @param string $subject
     * @return SubjectInterface|boolean
     */
    public function getSubject($subject)
    {
        if (isset($this->subjects[$subject])) {
            return $this->subjects[$subject];
        }
        return false;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function addSubject($subject)
    {
        $this->subjects[$subject] = new Subject($subject);
        return $this;
    }

    /**
     * @return SubjectManager
     */
    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new SubjectManager();
        }
        return self::$instance;
    }

    protected function __construct()
    {
        // singleton
    }

    protected function __clone()
    {
        // singleton
    }
}
