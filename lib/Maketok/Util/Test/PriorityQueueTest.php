<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Test;


use Maketok\Util\PriorityQueue;

class PriorityQueueTest extends \PHPUnit_Framework_TestCase
{
    /** @var  PriorityQueue */
    public $queue;

    public function setUp()
    {
        $this->queue = new PriorityQueue();
    }

    /**
     * @test
     * @covers Maketok\Util\PriorityQueue::insert
     */
    public function insert()
    {
        $data = 'test1';
        $data2 = 'test2';
        $data3 = 'test3';
        $this->queue->insert($data);
        $this->queue->insert($data2);
        $this->queue->insert($data3, 10);

        $this->assertEquals($data3, $this->queue->extract());
        $this->assertEquals($data, $this->queue->extract());
        $this->assertEquals($data2, $this->queue->extract());

        $this->assertTrue($this->queue->isEmpty());
    }

    /**
     * @test
     * @covers Maketok\Util\PriorityQueue::remove
     */
    public function remove()
    {
        $data = 'test1';
        $data2 = 'test2';
        $data3 = 'test3';
        $this->queue->insert($data);
        $this->queue->insert($data2);
        $this->queue->insert($data3, 10);

        $this->queue->remove($data3);

        $this->assertEquals($data, $this->queue->extract());
        $this->assertEquals($data2, $this->queue->extract());

        $this->assertTrue($this->queue->isEmpty());
    }
}
