<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util;

class PriorityQueue implements \ArrayAccess
{
    /**
     * the actual queue
     * @var array
     */
    protected $queue;

    /**
     * @var int
     */
    protected $serial = PHP_INT_MAX;

    /**
     * @param mixed $data
     * @param int $priority
     * @param int|string $offset
     */
    public function insert($data, $priority = 1, $offset = null)
    {
        if (is_null($offset)) {
            $this->queue[] = ['item' => $data, 'priority' => [$priority, $this->serial--]];
        } else {
            $this[$offset] = ['item' => $data, 'priority' => [$priority, $this->serial--]];
        }
        $this->adjustQueue();
    }

    /**
     * sort queue to make sure all members are on their priority places
     */
    protected function adjustQueue()
    {
        if (count($this->queue) <= 1) {
            return;
        }
        //@codeCoverageIgnoreStart
        uasort($this->queue, function ($a, $b) {
            if ($a['priority'] > $b['priority']) {
                return 1;
            } elseif ($a['priority'] < $b['priority']) {
                return -1;
            } else {
                return 0;
            }
        });
        //@codeCoverageIgnoreEnd
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->queue) <= 0;
    }

    /**
     * Extract a node from the inner queue and sift up
     *
     * @return mixed
     */
    public function extract()
    {
        $el = array_pop($this->queue);
        return $el['item'];
    }

    /**
     * remove item and dequeue it from internal queue
     * @param mixed $item
     * @param int|string $offset
     */
    public function remove($item = null, $offset = null)
    {
        if (is_null($item) && is_null($offset)) {
            throw new \InvalidArgumentException("Not enough arguments given.");
        }
        if (!is_null($offset)) {
            unset($this[$offset]);
        } elseif (!is_null($item)) {
            foreach ($this->queue as $key => $data) {
                $comparer = new ClosureComparer();
                if ($item == $data['item'] || $comparer->compare($item, $data['item']) === 0) {
                    unset($this[$key]);
                    break;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function offsetExists($offset)
    {
        return isset($this->queue[$offset]);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function offsetGet($offset)
    {
        return $this->queue[$offset]['item'];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->queue[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function offsetUnset($offset)
    {
        unset($this->queue[$offset]);
    }
}
