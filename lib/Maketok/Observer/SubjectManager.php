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

use Maketok\App\Site;
use Maketok\Util\ConfigGetter;
use Maketok\Util\PhpFileLoader;
use Maketok\Util\PriorityQueue;
use Symfony\Component\Config\FileLocator;

class SubjectManager implements SubjectManagerInterface
{
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
        $this->subscribers[(string) $subject]->insert($this->resolveSubscriber($subscriber, $subject), $priority);
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
            while ($subQueue->getQueue()->valid()) {
                /** @var SubscriberBag $subBag */
                $subBag = $subQueue->getQueue()->extract();
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
        if (is_callable($subscriber)) {
            if (is_object($subject) && ($subject instanceof SubjectInterface)) {
                return new SubscriberBag($subscriber, $subject);
            } else {
                return new SubscriberBag($subscriber, new Subject((string) $subject));
            }
        } elseif (is_object($subscriber) && ($subscriber instanceof SubscriberBag)) {
            return $subscriber;
        }
        throw new \InvalidArgumentException("Invalid subscriber given.");
    }

    /**
     * loads system subscribers
     * @codeCoverageIgnore
     */
    public function loadMap()
    {
        foreach (ConfigGetter::getConfig(Site::getConfig('subscribers_config_path'), 'subscribers', ENV) as $config) {
            $this->parseConfig($config);
        }
    }

    /**
     * @param array $config
     */
    protected function parseConfig($config)
    {
        foreach ($config as $event => $subscribers) {
            if (isset($subscribers['attach'])) {
                foreach ($subscribers['attach'] as $subscriberArray) {
                    if (is_array($subscriberArray) && isset($subscriberArray[0]) && isset($subscriberArray[1])) {
                        $subscriber = $subscriberArray[0];
                        $priority = $subscriberArray[1];
                        if (is_callable($subscriber)) {
                            $this->attach($event, $subscriber, $priority);
                        }
                    }
                }
            }
            if (isset($subscribers['detach'])) {
                foreach ($subscribers['detach'] as $subscriber) {
                    if (is_callable($subscriber)) {
                        $this->detach($event, $subscriber);
                    }
                }
            }
        }
    }
}
