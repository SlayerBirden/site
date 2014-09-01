<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Test;

use Maketok\Util\DirectoryHandler;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class DirectoryHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DirectoryHandler
     */
    private static $_dirHandler;

    /** @var  vfsStreamDirectory */
    private static  $root;

    public static function setUpBeforeClass()
    {
        self::$_dirHandler = new DirectoryHandler();
        self::$root = vfsStream::setup('root');
    }
    /**
     * @test
     */
    public function testMakeDir()
    {
        self::$_dirHandler->mkdir(vfsStream::url('root/tst/inner1'));
        $this->assertTrue(self::$root->hasChild('tst/inner1'));
    }

    /**
     * @test
     * @depends testMakeDir
     */
    public function testRmDir()
    {
        self::$_dirHandler->rm(vfsStream::url('root/tst/inner1'));
        $this->assertFalse(self::$root->hasChild('tst/inner1'));
    }

    /**
     * @test
     * @depends testRmDir
     */
    public function testLs()
    {
        self::$_dirHandler->mkdir(vfsStream::url('root/tst/inner1'));
        self::$_dirHandler->mkdir(vfsStream::url('root/tst/inner2'));
        self::$_dirHandler->mkdir(vfsStream::url('root/tst/inner3'));

        $this->assertEquals(array('inner1', 'inner2', 'inner3'), self::$_dirHandler->ls(vfsStream::url('root/tst/')));

        self::$_dirHandler->rm(vfsStream::url('root/tst/'));
        $this->assertFalse(self::$root->hasChild('tst'));
    }
}
