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

class PriorityQueue
{
    /**
     * the actual queue
     * @var \SplPriorityQueue
     */
    protected $queue;

    protected $items = array();

    protected $serial = PHP_INT_MAX;

    /**
     * @param mixed $data
     * @param int   $priority
     */
    public function insert($data, $priority = 1)
    {
        $this->items[] = array('item' => $data, 'priority' => $priority);
        // make sure we handle same priorities well
        $this->getQueue()->insert($data, array($priority, $this->serial--));
    }

    /**
     * @codeCoverageIgnore
     * @return \SplPriorityQueue
     */
    public function getQueue()
    {
        if (is_null($this->queue)) {
            $this->queue = new \SplPriorityQueue();
        }

        return $this->queue;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getQueue()->isEmpty();
    }

    /**
     * Extract a node from the inner queue and sift up
     *
     * @return mixed
     */
    public function extract()
    {
        return $this->getQueue()->extract();
    }

    /**
     * remove item and dequeue it from internal queue
     * @param mixed $item
     */
    public function remove($item)
    {
        $shouldRebuildQueue = false;
        foreach ($this->items as $key => $data) {
            $comparer = new ClosureComparer();
            if ($item == $data['item'] || $comparer->compare($item, $data['item']) === 0) {
                unset($this->items[$key]);
                $this->queue = null;
                $shouldRebuildQueue = true;
                break;
            }
        }
        if ($shouldRebuildQueue) {
            foreach ($this->items as $data) {
                $this->getQueue()->insert($data['item'], $data['priority']);
            }
        }
    }
}
