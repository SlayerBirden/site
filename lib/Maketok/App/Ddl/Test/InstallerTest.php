<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\App\Ddl;

class InstallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Installer
     */
    private static $_installer;

    /**
     * @var \ReflectionMethod
     */
    private static $_natRecursiveCompareReflectionMethod;

    public static function setUpBeforeClass()
    {
        self::$_installer = new Installer();
        self::$_natRecursiveCompareReflectionMethod = new \ReflectionMethod(get_class(self::$_installer), '_natRecursiveCompare');
        self::$_natRecursiveCompareReflectionMethod->setAccessible(true);
    }

    public function getPositive()
    {
        return array(
            array('1.0', '0.1.0'),
            array('0.1.1', '0.1.0'),
            array('0.2', '0.1.9'),
            array('1', '0.99999'),
        );
    }

    public function getNegative()
    {
        return array(
            array('0.1', '0.1.1'),
            array('0.2', '1.0'),
            array('22', '22.0.0.1'),
        );
    }

    public function getEquals()
    {
        return array(
            array('0.1', '0.1.0'),
            array('1', '1.0.0.0'),
            array('0.1.0.1.0', '0.1.0.1'),
            array('.0.1', '0.0.1'),
        );
    }

    /**
     * @test
     * @dataProvider getPositive
     */
    public function testNatRecursiveComparePositive($a, $b)
    {
        $this->assertEquals(1, self::$_natRecursiveCompareReflectionMethod->invoke(self::$_installer, $a, $b));
    }

    /**
     * @test
     * @dataProvider getNegative
     */
    public function testNatRecursiveCompareNegative($a, $b)
    {
        $this->assertEquals(-1, self::$_natRecursiveCompareReflectionMethod->invoke(self::$_installer, $a, $b));
    }

    /**
     * @test
     * @dataProvider getEquals
     */
    public function testNatRecursiveCompareEquals($a, $b)
    {
        $this->assertEquals(0, self::$_natRecursiveCompareReflectionMethod->invoke(self::$_installer, $a, $b));
    }
}