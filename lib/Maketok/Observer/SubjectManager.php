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
use Maketok\Util\ConfigConsumer;
use Maketok\Util\PriorityQueue;

class SubjectManager implements SubjectManagerInterface, ConfigConsumer
{
    use ContainerTrait;
    /**
     * @var PriorityQueue[]
     */
    private $subscribers = array();

    /**
     * {@inheritdoc}
     */
    public function attach($subject, $subscriber, $priority)
    {
        if (!isset($this->subscribers[(string) $subject])) {
            $this->subscribers[(string) $subject] = new PriorityQueue();
        }
        $sub = $this->resolveSubscriber($subscriber, $subject);
        $this->subscribers[(string) $subject]->insert($sub, $priority, $sub->code);
    }

    /**
     * {@inheritdoc}
     */
    public function detach($subject, $subscriber)
    {
        if (isset($this->subscribers[(string) $subject])) {
            $this->subscribers[(string) $subject]->remove($this->resolveSubscriber($subscriber, $subject));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function notify($subject, StateInterface $state)
    {
        if (isset($this->subscribers[$subject])) {
            /** @var PriorityQueue $_subQueue */
            $subQueue = $this->subscribers[$subject];
            while (!$subQueue->isEmpty()) {
                /** @var SubscriberBag $subBag */
                $subBag = $subQueue->extract();
                call_user_func($subBag->subscriber, $state->setSubject($subBag->subject));
                if ($subBag->subject->getShouldStopPropagation()) {
                    break;
                }
            }
        }
    }

    /**
     * @param  mixed                     $subscriber
     * @param  mixed                     $subject
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
        // from now on we don't need string sub
        if (!is_object($subject) || !($subject instanceof SubjectInterface)) {
            $subject = new Subject((string) $subject);
        }
        if (is_array($subscriber) && !is_callable($subscriber)) {
            // id => sub pair
            if (is_callable(current($subscriber))) {
                return new SubscriberBag(key($subscriber), current($subscriber), $subject);
            }
        } elseif (is_callable($subscriber)) {
            // only sub
            $hasher = new CallableHash();
            return new SubscriberBag($hasher->getHash($subscriber), $subscriber, $subject);
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
        foreach ($this->ioc()->get('config_getter')
                     ->getConfig(Site::getConfig('subscribers_config_path'), 'subscribers', ENV) as $config) {
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
