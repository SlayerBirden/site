<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Test;

use Maketok\Util\StreamHandler;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class StreamHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StreamHandler
     */
    private static $_streamHandler;

    /** @var  vfsStreamDirectory */
    private static  $root;

    public static function setUpBeforeClass()
    {
        self::$_streamHandler = new StreamHandler();
        self::$root = vfsStream::setup('root');
    }

    public function tearDown()
    {
        self::$_streamHandler->close();
    }

    /**
     * @test
     * @covers Maketok\Util\StreamHandler::write
     */
    public function testWriteSingleFile()
    {
        self::$_streamHandler->write('test1', vfsStream::url('root/test.txt'));
        $this->assertTrue(self::$root->hasChild('test.txt'), 'File does not exist!');
    }

    /**
     * @test
     * @covers Maketok\Util\StreamHandler::write
     */
    public function testWriteDirFile()
    {
        self::$_streamHandler->write('test2', vfsStream::url('root/trash/test.txt'));
        $this->assertTrue(self::$root->hasChild('trash/test.txt'), 'File does not exist!');
        $this->assertTrue(is_dir(vfsStream::url('root/trash')));
    }

    /**
     * @test
     * @depends testWriteSingleFile
     * @depends testWriteDirFile
     * @covers Maketok\Util\StreamHandler::read
     */
    public function testRead()
    {
        $actual = self::$_streamHandler->read(null, vfsStream::url('root/test.txt'));
        $expected = 'test1';
        $this->assertEquals($expected, $actual);
        self::$_streamHandler->close();
        $actual2 = self::$_streamHandler->read(5, vfsStream::url('root/trash/test.txt'));
        $expected2 = 'test2';
        $this->assertEquals($expected2, $actual2);
    }

    /**
     * @test
     * @covers Maketok\Util\StreamHandler::read
     */
    public function testEof()
    {
        self::$_streamHandler->setPath(vfsStream::url('root/test.txt'));
        self::$_streamHandler->read(1);
        $this->assertFalse(self::$_streamHandler->eof());
        self::$_streamHandler->read();
        $this->assertTrue(self::$_streamHandler->eof());
    }

    /**
     * @test
     * @depends testWriteSingleFile
     * @covers Maketok\Util\StreamHandler::delete
     */
    public function testDeleteSingleFile()
    {
        self::$_streamHandler->delete(vfsStream::url('root/test.txt'));
        $this->assertFalse(self::$root->hasChild('text.txt'));
    }

    /**
     * @test
     * @depends testDeleteSingleFile
     * @covers Maketok\Util\StreamHandler::delete
     */
    public function testDeleteDir()
    {
        self::$_streamHandler->delete(vfsStream::url('root/trash/test.txt'), 1);
        $this->assertFalse(self::$root->hasChild('trash/text.txt'));
        $this->assertFalse(self::$root->hasChild('trash'));
    }

    /**
     * @test
     * @depends testWriteDirFile
     * @covers Maketok\Util\StreamHandler::lock
     */
    public function testLock()
    {
        self::$_streamHandler->setPath(vfsStream::url('root/testLock/test.txt'));
        $this->assertTrue(self::$_streamHandler->lock());
        $expected = 'stream1';
        self::$_streamHandler->write($expected);
        $stream2 = new StreamHandler();
        $stream2->setPath(vfsStream::url('root/testLock/test.txt'));
        $this->assertFalse($stream2->lock());
    }

    /**
     * @test
     * @depends testLock
     * @covers Maketok\Util\StreamHandler::unLock
     */
    public function testUnlock()
    {
        self::$_streamHandler->lock(vfsStream::url('root/test_lock.txt'));
        self::$_streamHandler->write('test');
        $this->assertTrue(self::$_streamHandler->unLock());
        $stream2 = new StreamHandler();
        $stream2->setPath(vfsStream::url('root/test_lock.txt'));
        $this->assertTrue($stream2->lock());
    }

    /**
     * @test
     * @depends testLock
     * @depends testUnlock
     * @covers Maketok\Util\StreamHandler::delete
     */
    public function testDelete()
    {
        $this->assertTrue(self::$_streamHandler->delete(vfsStream::url('root/test_lock.txt')));
        $this->assertTrue(self::$_streamHandler->delete(vfsStream::url('root/testLock/test.txt'), 1));
    }
}
