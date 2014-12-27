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

class SubjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testAddSame()
    {
        $manager = new SubjectManager();
        $mock = $this->getMock('MuteStub', ['doSomething']);
        $mock->expects($this->once())->method('doSomething');
        $manager->attach('simpleEvent', ['first' => [$mock, 'doSomething']], 1);
        $manager->attach('simpleEvent', ['second' => [$mock, 'doSomething']], 2);
        $manager->detach('simpleEvent', 'first');
        $manager->notify('simpleEvent', new State());
    }

    /**
     * @test
     */
    public function testAddDifferent()
    {
        $manager = new SubjectManager();
        $mock = $this->getMock('MuteStub', ['doSomething']);
        $mock->expects($this->once())->method('doSomething');
        $mock2 = $this->getMock('MuteStub', ['doSomethingElse']);
        $mock2->expects($this->once())->method('doSomethingElse');
        $manager->attach('simpleEvent', [$mock, 'doSomething'], 1);
        $manager->attach('simpleEvent', [$mock2, 'doSomethingElse'], 2);
        $manager->notify('simpleEvent', new State());
    }

    /**
     * @test
     */
    public function testNoPropagation()
    {
        $manager = new SubjectManager();
        $mock = $this->getMock('MuteStub', ['doSomething']);
        $mock->expects($this->once())->method('doSomething');
        $mock2 = $this->getMock('MuteStub', ['doSomethingElse']);
        $mock2->expects($this->never())->method('doSomethingElse');
        $subject = new Subject('simpleEvent');
        $manager->attach($subject->setShouldStopPropagation(1), [$mock, 'doSomething'], 999);
        $manager->attach('simpleEvent', [$mock2, 'doSomethingElse'], 2);
        $manager->notify('simpleEvent', new State());
    }

    /**
     * @test
     */
    public function testResolver()
    {
        $manager = new SubjectManager();
        $mock = $this->getMock('MuteStub', ['doSomething']);
        $mock->expects($this->once())->method('doSomething');
        $mock2 = $this->getMock('MuteStub', ['doSomethingElse']);
        $mock2->expects($this->never())->method('doSomethingElse');
        $subject = new Subject('simpleEvent');
        $manager->attach($subject, new SubscriberBag(
            'test',
            [$mock, 'doSomething'],
            $subject->setShouldStopPropagation(1)
        ), 999);
        $manager->attach('simpleEvent', [$mock2, 'doSomethingElse'], 2);
        $manager->notify('simpleEvent', new State());
    }

    /**
     * @test
     */
    public function testNoSubs()
    {
        $manager = new SubjectManager();
        $mock = $this->getMock('MuteStub', ['doSomething']);
        $mock->expects($this->never())->method('doSomething');
        $subject = new Subject('simpleEvent');
        $manager->attach($subject, new SubscriberBag(
            'test',
            [$mock, 'doSomething'],
            $subject->setShouldStopPropagation(1)
        ), 999);
        $manager->detach('simpleEvent', 'test');
        $manager->notify('simpleEvent', new State());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid subscriber given.
     */
    public function testResolverInvalidSubscriber()
    {
        $manager = new SubjectManager();
        $manager->attach('simpleEvent', new \stdClass(), 2);
    }

    /**
     * @test
     */
    public function parseConfig()
    {
        $mock = $this->getMock('MuteStub', ['doSomething']);
        $mock->expects($this->never())->method('doSomething');
        $config = [
            'test_event' => [
                'attach' => [
                    [
                        ['test_sub' => [$mock, 'doSomething']], 0
                    ]
                ],
                'detach' => ['test_sub']
            ]
        ];
        $manager = new SubjectManager();
        $manager->parseConfig($config);
        $manager->notify('test_event', new State());
    }
}
