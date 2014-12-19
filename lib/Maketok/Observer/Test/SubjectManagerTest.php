<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Observer\Test;

use Maketok\Observer\State;
use Maketok\Observer\Subject;
use Maketok\Observer\SubjectManager;
use Maketok\Observer\SubscriberBag;

/**
 * @coversDefaultClass \Maketok\Observer\SubjectManager
 */
class SubjectManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers ::attach
     * @covers ::detach
     * @covers ::notify
     * @covers ::resolveSubscriber
     */
    public function testAddSame()
    {
        $manager = new SubjectManager();
        $mock = $this->getMock('\Maketok\Observer\Test\MuteStub');
        $mock->expects($this->once())->method('doSomething');
        $manager->attach('simpleEvent', [$mock, 'doSomething'], 1);
        $manager->attach('simpleEvent', [$mock, 'doSomething'], 2);
        $manager->detach('simpleEvent', [$mock, 'doSomething']);
        $manager->notify('simpleEvent', new State());
    }

    /**
     * @test
     * @covers ::attach
     * @covers ::notify
     * @covers ::resolveSubscriber
     */
    public function testAddDifferent()
    {
        $manager = new SubjectManager();
        $mock = $this->getMock('\Maketok\Observer\Test\MuteStub');
        $mock->expects($this->once())->method('doSomething');
        $mock2 = $this->getMock('\Maketok\Observer\Test\MuteStub');
        $mock2->expects($this->once())->method('doSomethingElse');
        $manager->attach('simpleEvent', [$mock, 'doSomething'], 1);
        $manager->attach('simpleEvent', [$mock2, 'doSomethingElse'], 2);
        $manager->notify('simpleEvent', new State());
    }

    /**
     * @test
     * @covers ::attach
     * @covers ::notify
     * @covers ::resolveSubscriber
     */
    public function testNoPropagation()
    {
        $manager = new SubjectManager();
        $mock = $this->getMock('\Maketok\Observer\Test\MuteStub');
        $mock->expects($this->once())->method('doSomething');
        $mock2 = $this->getMock('\Maketok\Observer\Test\MuteStub');
        $mock2->expects($this->never())->method('doSomethingElse');
        $subject = new Subject('simpleEvent');
        $manager->attach($subject->setShouldStopPropagation(1), [$mock, 'doSomething'], 999);
        $manager->attach('simpleEvent', [$mock2, 'doSomethingElse'], 2);
        $manager->notify('simpleEvent', new State());
    }

    /**
     * @test
     * @covers ::attach
     * @covers ::notify
     * @covers ::resolveSubscriber
     */
    public function testResolver()
    {
        $manager = new SubjectManager();
        $mock = $this->getMock('\Maketok\Observer\Test\MuteStub');
        $mock->expects($this->once())->method('doSomething');
        $mock2 = $this->getMock('\Maketok\Observer\Test\MuteStub');
        $mock2->expects($this->never())->method('doSomethingElse');
        $subject = new Subject('simpleEvent');
        $manager->attach($subject, new SubscriberBag([$mock, 'doSomething'], $subject->setShouldStopPropagation(1)), 999);
        $manager->attach('simpleEvent', [$mock2, 'doSomethingElse'], 2);
        $manager->notify('simpleEvent', new State());
    }

    /**
     * @test
     * @covers ::attach
     * @covers ::resolveSubscriber
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid subscriber given.
     */
    public function testResolverInvalidSubscriber()
    {
        $manager = new SubjectManager();
        $manager->attach('simpleEvent', new \stdClass(), 2);
    }
}
