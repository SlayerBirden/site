<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Util;

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
     */
    public function insert()
    {
        $data = 'SomeClass::staticMethod';
        $data2 = [new \stdClass(), 'method'];
        $data3 = function () {echo 'closure';};
        $this->queue->insert($data);
        $this->queue->insert($data2);
        $this->queue->insert($data3, 10);
        $this->queue->insert($data3, -1, 'key');

        $this->assertEquals($data3, $this->queue->extract());
        $this->assertEquals($data, $this->queue->extract());
        $this->assertEquals($data2, $this->queue->extract());
        $this->assertEquals($data3, $this->queue->extract());

        $this->assertTrue($this->queue->isEmpty());
    }

    /**
     * @test
     */
    public function remove()
    {
        $data = 'SomeClass::staticMethod';
        $data2 = [new \stdClass(), 'method'];
        $data3 = function () {echo 'closure';};
        $this->queue->insert($data);
        $this->queue->insert($data2);
        $this->queue->insert($data3, 10, 'testkey');

        $this->queue->remove('SomeClass::staticMethod');
        $this->queue->remove([new \stdClass(), 'method']);
        $this->queue->remove(null, 'testkey');

        $this->assertTrue($this->queue->isEmpty());
    }
}
