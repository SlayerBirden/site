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

use Maketok\Util\DirectoryHandler;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @coversDefaultClass \Maketok\Util\DirectoryHandler
 */
class DirectoryHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DirectoryHandler
     */
    private static $_dirHandler;

    /** @var  vfsStreamDirectory */
    private static $root;

    public static function setUpBeforeClass()
    {
        self::$_dirHandler = new DirectoryHandler();
        self::$root = vfsStream::setup('root');
    }
    /**
     * @test
     * @covers ::mkdir
     */
    public function testMakeDir()
    {
        self::$_dirHandler->mkdir(vfsStream::url('root/tst/inner1'));
        $this->assertTrue(self::$root->hasChild('tst/inner1'));
    }

    /**
     * @test
     * @depends testMakeDir
     * @covers ::rm
     * @covers ::mkdir
     */
    public function testRmDir()
    {
        self::$_dirHandler->rm(vfsStream::url('root/tst/inner1'));
        $this->assertFalse(self::$root->hasChild('tst/inner1'));

        self::$_dirHandler->mkdir(vfsStream::url('root/tst/inner1'));

        $h = fopen(vfsStream::url('root/tst/inner1/test.txt'), 'w');
        fwrite($h, 'test');
        fclose($h);

        self::$_dirHandler->rm(vfsStream::url('root/tst/inner1'));
    }

    /**
     * @test
     * @depends testRmDir
     * @covers ::ls
     * @covers ::rm
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

    /**
     * @test
     * @covers ::ls
     * @depends testLs
     * @expectedException \Maketok\Util\Exception\DirectoryException
     * @expectedExceptionMessage The path does not exist.
     */
    public function testLsException()
    {
        self::$_dirHandler->ls(vfsStream::url('root/tst/'));
    }
}
