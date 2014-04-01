<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Util;

class DirectoryHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DirectoryHandler
     */
    private static $_dirHandler;

    public static function setUpBeforeClass()
    {
        self::$_dirHandler = new DirectoryHandler();
    }
    /**
     * @test
     */
    public function testMakeDir()
    {
        self::$_dirHandler->mkdir('tst/inner1');
        $this->assertTrue(is_dir('tst/inner1'));
    }

    /**
     * @test
     * @depends testMakeDir
     */
    public function testRmDir()
    {
        self::$_dirHandler->rm('tst/inner1');
        $this->assertFalse(is_dir('tst/inner1'));
    }

    /**
     * @test
     * @depends testRmDir
     */
    public function testLs()
    {
        self::$_dirHandler->mkdir('tst/inner1');
        self::$_dirHandler->mkdir('tst/inner2');
        self::$_dirHandler->mkdir('tst/inner3');

        $this->assertEquals(array('inner1', 'inner2', 'inner3'), self::$_dirHandler->ls('tst'));

        self::$_dirHandler->rm('tst');
        $this->assertFalse(is_dir('tst'));
    }
}