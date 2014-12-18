<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util\Test;

use Maketok\Util\StreamHandler;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @coversDefaultClass \Maketok\Util\StreamHandler
 */
class StreamHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StreamHandler
     */
    private static $streamHandler;

    /** @var  vfsStreamDirectory */
    private static  $root;

    public static function setUpBeforeClass()
    {
        self::$streamHandler = new StreamHandler();
        self::$root = vfsStream::setup('root');
    }

    public function tearDown()
    {
        self::$streamHandler->close();
    }

    /**
     * @test
     * @covers ::write
     * @covers ::initHandle
     */
    public function testWriteSingleFile()
    {
        self::$streamHandler->write('test1', vfsStream::url('root/test.txt'));
        $this->assertTrue(self::$root->hasChild('test.txt'), 'File does not exist!');
    }

    /**
     * @test
     * @covers ::writeWithLock
     * @covers ::initHandle
     */
    public function testWriteWithLock()
    {
        self::$streamHandler->writeWithLock('test1', vfsStream::url('root/test.txt'));
        $this->assertTrue(self::$root->hasChild('test.txt'), 'File does not exist!');
    }

    /**
     * @test
     * @covers ::setPath
     * @covers ::pwd
     */
    public function testPwd()
    {
        self::$streamHandler->setPath(vfsStream::url('root'));
        $this->assertEquals(vfsStream::url('root'), self::$streamHandler->pwd());
    }

    /**
     * @test
     * @covers ::write
     * @covers ::initHandle
     */
    public function testWriteDirFile()
    {
        self::$streamHandler->write('test2', vfsStream::url('root/trash/test.txt'));
        $this->assertTrue(self::$root->hasChild('trash/test.txt'), 'File does not exist!');
        $this->assertTrue(is_dir(vfsStream::url('root/trash')));
    }

    /**
     * @test
     * @depends testWriteSingleFile
     * @depends testWriteDirFile
     * @covers ::read
     * @covers ::initHandle
     * @covers ::close
     */
    public function testRead()
    {
        $actual = self::$streamHandler->read(null, vfsStream::url('root/test.txt'));
        $expected = 'test1';
        $this->assertEquals($expected, $actual);
        self::$streamHandler->close();
        $actual2 = self::$streamHandler->read(5, vfsStream::url('root/trash/test.txt'));
        $expected2 = 'test2';
        $this->assertEquals($expected2, $actual2);
    }

    /**
     * @test
     * @covers ::read
     * @covers ::initHandle
     * @covers ::eof
     */
    public function testEof()
    {
        self::$streamHandler->setPath(vfsStream::url('root/test.txt'));
        self::$streamHandler->read(1);
        $this->assertFalse(self::$streamHandler->eof());
        self::$streamHandler->read();
        $this->assertTrue(self::$streamHandler->eof());
    }

    /**
     * @test
     * @depends testWriteSingleFile
     * @covers ::delete
     */
    public function testDeleteSingleFile()
    {
        self::$streamHandler->delete(vfsStream::url('root/test.txt'));
        $this->assertFalse(self::$root->hasChild('text.txt'));
    }

    /**
     * @test
     * @depends testDeleteSingleFile
     * @covers ::delete
     */
    public function testDeleteDir()
    {
        self::$streamHandler->delete(vfsStream::url('root/trash/test.txt'), 1);
        $this->assertFalse(self::$root->hasChild('trash/text.txt'));
        $this->assertFalse(self::$root->hasChild('trash'));
    }

    /**
     * @test
     * @depends testWriteDirFile
     * @covers ::initHandle
     * @covers ::lock
     * @covers ::setPath
     */
    public function testLock()
    {
        self::$streamHandler->setPath(vfsStream::url('root/testLock/test.txt'));
        $this->assertTrue(self::$streamHandler->lock());
        $expected = 'stream1';
        self::$streamHandler->write($expected);
        $stream2 = new StreamHandler();
        $stream2->setPath(vfsStream::url('root/testLock/test.txt'));
        $this->assertFalse($stream2->lock());
    }

    /**
     * @test
     * @depends testLock
     * @covers ::unLock
     * @covers ::setPath
     */
    public function testUnlock()
    {
        self::$streamHandler->lock(vfsStream::url('root/test_lock.txt'));
        self::$streamHandler->write('test');
        $this->assertTrue(self::$streamHandler->unLock());
        $stream2 = new StreamHandler();
        $stream2->setPath(vfsStream::url('root/test_lock.txt'));
        $this->assertTrue($stream2->lock());
    }

    /**
     * @test
     * @depends testLock
     * @depends testUnlock
     * @covers ::delete
     */
    public function testDelete()
    {
        $this->assertTrue(self::$streamHandler->delete(vfsStream::url('root/test_lock.txt')));
        $this->assertTrue(self::$streamHandler->delete(vfsStream::url('root/testLock/test.txt'), 1));
    }
}
