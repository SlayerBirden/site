<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;

class PriorityQueue
{
    /**
     * the actual queue
     * @var \SplPriorityQueue
     */
    protected $_queue;

    protected $_items = array();

    protected $_serial = PHP_INT_MAX;

    /**
     * @param mixed $data
     * @param int $priority
     */
    public function insert($data, $priority = 1)
    {
        $this->_items[] = array('item' => $data, 'priority' => $priority);
        // make sure we handle same priorities well
        $this->getQueue()->insert($data, array($priority, $this->_serial--));
    }

    /**
     * @codeCoverageIgnore
     * @return \SplPriorityQueue
     */
    public function getQueue()
    {
        if (is_null($this->_queue)) {
            $this->_queue = new \SplPriorityQueue();
        }
        return $this->_queue;
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
     * @param $item
     */
    public function remove($item)
    {
        $shouldRebuildQueue = false;
        foreach ($this->_items as $key => $data) {
            if ($item === $data['item']) {
                unset($this->_items[$key]);
                $this->_queue = null;
                $shouldRebuildQueue = true;
                break;
            }
        }
        if ($shouldRebuildQueue) {
            foreach ($this->_items as $data) {
                $this->getQueue()->insert($data['item'], $data['priority']);
            }
        }
    }
}
