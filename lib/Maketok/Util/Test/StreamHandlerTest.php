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
        self::$_streamHandler->write('test2', 'test/test.txt');
        $this->assertFileExists('test/test.txt', 'File does not exist!');
        $this->assertTrue(is_dir('test'));
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
        $actual2 = self::$_streamHandler->read(5, 'test/test.txt');
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
        self::$_streamHandler->delete('test/test.txt', 1);
        $this->assertFileNotExists('test/text.txt');
        $this->assertFalse(is_dir('test'));
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
        $actual = self::$_streamHandler->read(null, 'testLock/test.txt');
        $this->assertEquals($expected, $actual);
        $stream2 = new StreamHandler();
        $stream2->setPath('testLock/test.txt');
        $this->assertFalse($stream2->lock());
        $this->assertFalse($stream2->write('stream2'));
        $actual2 = $stream2->read('testLock/test.txt');
        $this->assertEquals($expected, $actual2);
    }

    /**
     * @test
     * @depends testLock
     */
    public function testUnlock()
    {
        $this->assertTrue(self::$_streamHandler->unLock());
        $stream2 = new StreamHandler();
        $stream2->setPath('testLock/test.txt');
        $this->assertTrue($stream2->lock());
        $this->assertTrue($stream2->write('123'));
        $this->assertTrue($stream2->unLock());
        $this->assertTrue($stream2->delete(null, 1));
    }
}