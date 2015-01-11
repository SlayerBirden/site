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

use Maketok\App\Helper\ContainerTrait;
use Maketok\App\Site;
use Maketok\Util\CallableHash;
use Maketok\Util\ConfigConsumerInterface;
use Maketok\Util\PriorityQueue;

class SubjectManager implements SubjectManagerInterface, ConfigConsumerInterface
{
    use ContainerTrait;

    /**
     * @var \SplObjectStorage|PriorityQueue[]
     */
    private $subscribers;

    /**
     * @var SubjectInterface[]
     */
    private $subjects = [];

    /**
     * init sub array
     */
    public function __construct()
    {
        $this->subscribers = new \SplObjectStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function attach($subject, $subscriber, $priority)
    {
        $subject = $this->resolveSubject($subject);
        if (!isset($this->subscribers[$subject])) {
            $this->subscribers[$subject] = new PriorityQueue();
        }
        $sub = $this->resolveSubscriber($subscriber, $subject);
        $this->subscribers[$subject]->insert($sub, $priority, $sub->code);
    }

    /**
     * @param mixed $subject
     * @return SubjectInterface
     */
    protected function resolveSubject($subject)
    {
        if (!isset($this->subjects[(string) $subject])) {
            $subject = new Subject((string) $subject);
            $this->subjects[$subject->__toString()] = $subject;
        }
        return $this->subjects[(string) $subject];
    }

    /**
     * {@inheritdoc}
     */
    public function detach($subject, $subscriber)
    {
        $subject = $this->resolveSubject($subject);
        if (isset($this->subscribers[$subject])) {
            $this->subscribers[$subject]->remove($this->resolveSubscriber($subscriber, $subject));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function notify($subject, StateInterface $state)
    {
        $subject = $this->resolveSubject($subject);
        if (isset($this->subscribers[$subject])) {
            /** @var PriorityQueue $_subQueue */
            $subQueue = $this->subscribers[$subject];
            while (!$subQueue->isEmpty()) {
                /** @var SubscriberBag $subBag */
                $subBag = $subQueue->extract();
                $arguments = [];
                $state->setSubject($subject);
                foreach ($state as $argument) {
                    $arguments[] = $argument;
                }
                call_user_func_array($subBag->subscriber, $arguments);
                if ($subject->getShouldStopPropagation()) {
                    break;
                }
            }
        }
    }

    /**
     * @param  mixed $subscriber
     * @param  SubjectInterface $subject
     * @throws \InvalidArgumentException
     * @return SubscriberBag
     */
    protected function resolveSubscriber($subscriber, $subject)
    {
        if (is_string($subscriber)) {
            if (isset($this->subscribers[$subject][$subscriber])) {
                return $this->subscribers[$subject][$subscriber];
            } else {
                throw new \InvalidArgumentException(sprintf("Can't find subscriber by id %s.", $subscriber));
            }
        }
        if (is_array($subscriber) && !is_callable($subscriber)) {
            // id => sub pair
            if (is_callable(current($subscriber))) {
                return new SubscriberBag(key($subscriber), current($subscriber));
            }
        } elseif (is_callable($subscriber)) {
            // only sub
            $hasher = new CallableHash();
            return new SubscriberBag($hasher->getHash($subscriber), $subscriber);
        } elseif (is_object($subscriber) && ($subscriber instanceof SubscriberBag)) {
            // bag already
            return $subscriber;
        }
        throw new \InvalidArgumentException("Invalid subscriber given.");
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function initConfig()
    {
        $env = $this->ioc()->get('request')->getArea();
        $configs = $this->ioc()->get('config_getter')->getConfig(
            Site::getConfig('subscribers_config_path'),
            'subscribers',
            $env
        );
        foreach ($configs as $config) {
            $this->parseConfig($config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parseConfig(array $config)
    {
        foreach ($config as $event => $subscribers) {
            if (isset($subscribers['attach'])) {
                foreach ($subscribers['attach'] as $subscriberArray) {
                    if (is_array($subscriberArray) && isset($subscriberArray[0]) && isset($subscriberArray[1])) {
                        $subscriber = $subscriberArray[0];
                        $priority = $subscriberArray[1];
                        $this->attach($event, $subscriber, $priority);
                    }
                }
            }
            if (isset($subscribers['detach'])) {
                foreach ($subscribers['detach'] as $subscriber) {
                    $this->detach($event, $subscriber);
                }
            }
        }
    }
}
