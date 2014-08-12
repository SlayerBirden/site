<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Util;

class StreamHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StreamHandler
     */
    private static $_streamHandler;

    public static function setUpBeforeClass()
    {
        self::$_streamHandler = new StreamHandler();
    }

    public function tearDown()
    {
        self::$_streamHandler->close();
    }

    /**
     * @test
     */
    public function testWriteSingleFile()
    {
        self::$_streamHandler->write('test1', 'test.txt');
        $this->assertFileExists('test.txt', 'File does not exist!');
    }

    /**
     * @test
     */
    public function testWriteDirFile()
    {
        self::$_streamHandler->write('test2', 'trash/test.txt');
        $this->assertFileExists('trash/test.txt', 'File does not exist!');
        $this->assertTrue(is_dir('trash'));
    }

    /**
     * @test
     * @depends testWriteSingleFile
     * @depends testWriteDirFile
     */
    public function testRead()
    {
        $actual = self::$_streamHandler->read(null, 'test.txt');
        $expected = 'test1';
        $this->assertEquals($expected, $actual);
        self::$_streamHandler->close();
        $actual2 = self::$_streamHandler->read(5, 'trash/test.txt');
        $expected2 = 'test2';
        $this->assertEquals($expected2, $actual2);
    }

    /**
     * @test
     */
    public function testEof()
    {
        self::$_streamHandler->setPath('test.txt');
        self::$_streamHandler->read(1);
        $this->assertFalse(self::$_streamHandler->eof());
        self::$_streamHandler->read();
        $this->assertTrue(self::$_streamHandler->eof());
    }

    /**
     * @test
     * @depends testWriteSingleFile
     */
    public function testDeleteSingleFile()
    {
        self::$_streamHandler->delete('test.txt');
        $this->assertFileNotExists('text.txt');
    }

    /**
     * @test
     * @depends testDeleteSingleFile
     */
    public function testDeleteDir()
    {
        self::$_streamHandler->delete('trash/test.txt', 1);
        $this->assertFileNotExists('trash/text.txt');
        $this->assertFalse(is_dir('trash'));
    }

    /**
     * @test
     * @depends testWriteDirFile
     */
    public function testLock()
    {
        self::$_streamHandler->setPath('testLock/test.txt');
        $this->assertTrue(self::$_streamHandler->lock());
        $expected = 'stream1';
        self::$_streamHandler->write($expected);
        $stream2 = new StreamHandler();
        $stream2->setPath('testLock/test.txt');
        $this->assertFalse($stream2->lock());
    }

    /**
     * @test
     * @depends testLock
     */
    public function testUnlock()
    {
        self::$_streamHandler->lock('test_lock.txt');
        self::$_streamHandler->write('test');
        $this->assertTrue(self::$_streamHandler->unLock());
        $stream2 = new StreamHandler();
        $stream2->setPath('test_lock.txt');
        $this->assertTrue($stream2->lock());
    }

    /**
     * @test
     * @depends testLock
     * @depends testUnlock
     */
    public function testDelete()
    {
        $this->assertTrue(self::$_streamHandler->delete('test_lock.txt'));
        $this->assertTrue(self::$_streamHandler->delete('testLock/test.txt', 1));
    }
}