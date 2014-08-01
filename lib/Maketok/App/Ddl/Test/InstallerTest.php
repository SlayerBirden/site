<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\App\Ddl\Test;

use Maketok\App\Ddl\Test\Tool\DdlCheck;
use Maketok\App\Ddl\Test\Tool\TableIterationOne;
use Maketok\App\Ddl\Test\Tool\TableIterationTwo;
use Maketok\App\Ddl\Installer;
use Maketok\App\Site;
use Maketok\Util\StreamHandler;
use Zend\Db\Adapter\Adapter;

class InstallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Installer
     */
    private static $_installer;

    private static $_installerLockName = 'test_ddl_installer.lock';

    /**
     * @var DdlCheck
     */
    private static $_checker;

    /**
     * @var \ReflectionMethod
     */
    private static $_natRecursiveCompareReflectionMethod;

    public static function setUpBeforeClass()
    {
        self::$_installer = new Installer();
        self::$_installer->setInstallerLockName(self::$_installerLockName);
        self::$_natRecursiveCompareReflectionMethod = new \ReflectionMethod(get_class(self::$_installer), '_natRecursiveCompare');
        self::$_natRecursiveCompareReflectionMethod->setAccessible(true);

        self::$_checker = new DdlCheck();
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

    /**
     * @test
     */
    public function testProcessClients()
    {
        self::$_installer->addClient(new TableIterationOne());
        self::$_installer->processClients();

        $result = self::$_checker->checkTable('table_one');
        $this->assertNotEmpty($result);

        $this->assertCount(7, $result['columns']);
        $this->assertCount(1, $result['indexes']);
        $this->assertCount(2, $result['constraints']);

        $result = self::$_checker->checkColumn('table_one', 'raw_data');
        $this->assertNotEmpty($result);

        $this->assertEquals('raw_data', $result['name']);
        $this->assertEquals('blob', $result['type']);
        $this->assertFalse($result['nullable']);
        $this->assertFalse(isset($result['auto_increment']));

        $result = self::$_checker->checkColumn('table_one', 'id');
        $this->assertNotEmpty($result);

        $this->assertEquals('id', $result['name']);
        $this->assertEquals('int', $result['type']);
        $this->assertFalse($result['nullable']);
        $this->assertFalse(isset($result['unsigned']));
        $this->assertFalse(isset($result['auto_increment']));

        $result = self::$_checker->checkColumn('table_one', 'created_at');
        $this->assertNotEmpty($result);

        $this->assertEquals('created_at', $result['name']);
        $this->assertEquals('datetime', $result['type']);

        $result = self::$_checker->checkIndex('table_one', 'KEY_FLAG');
        $this->assertNotEmpty($result);

        $this->assertEquals('KEY_FLAG', $result['name']);
        $this->assertEquals(array('flag'), $result['definition']);

        // ==================== table 2

        $result = self::$_checker->checkTable('table_two');
        $this->assertNotEmpty($result);

        $this->assertCount(4, $result['columns']);
        $this->assertCount(1, $result['indexes']);
        $this->assertCount(2, $result['constraints']);

        $result = self::$_checker->checkColumn('table_one', 'title');
        $this->assertNotEmpty($result);

        print_r($result);

        $this->assertEquals('title', $result['name']);
        $this->assertEquals('varchar', $result['type']);
        $this->assertFalse($result['nullable']);
        $this->assertEquals('123', $result['default']);

        $fk = $result['constraints'][1];
        $this->assertNotEmpty($fk);
        $this->assertEquals('foreign_key', $fk['type']);
        $this->assertEquals('FK_KEY_UNIQUE_CODE', $fk['name']);
        $this->assertEquals('parent_id', $fk['column']);
        $this->assertEquals('table_one', $fk['reference_table']);
        $this->assertEquals('id', $fk['reference_column']);
        $this->assertEquals('CASCADE', $fk['on_delete']);
        $this->assertEquals('CASCADE', $fk['on_update']);
    }

    /**
     * @test
     * @depends testProcessClients
     */
    public function testProcessClientsUpdate()
    {
        self::$_installer->addClient(new TableIterationTwo());
        self::$_installer->processClients();

        $result = self::$_checker->checkTable('table_one');
        $this->assertNotEmpty($result);

        $this->assertCount(6, $result['columns']);
        $this->assertTrue(!isset($result['indexes']));
        $this->assertCount(2, $result['constraints']);

        $result = self::$_checker->checkColumn('table_one', 'id');
        $this->assertNotEmpty($result);
        $this->assertEquals('id', $result['name']);
        $this->assertEquals('int', $result['type']);
        $this->assertFalse($result['nullable']);
        $this->assertTrue($result['unsigned']);
        $this->assertTrue($result['auto_increment']);

        // ==================== table 2

        $result = self::$_checker->checkTable('table_two');
        $this->assertNotEmpty($result);

        $this->assertCount(5, $result['columns']);
        $this->assertCount(2, $result['indexes']);
        $this->assertCount(1, $result['constraints']);

        $result = self::$_checker->checkColumn('table_two', 'flag');
        $this->assertNotEmpty($result);
        $this->assertFalse(isset($result['unsigned']));



    }

    public static function tearDownAfterClass()
    {
        // clean up
        $fullPath = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'locks' . DIRECTORY_SEPARATOR . self::$_installerLockName;
        $sh = new StreamHandler();
        $sh->setPath($fullPath);
        $sh->writeWithLock('');

        $sql = <<<'SQL'
DROP TABLE IF EXISTS `table_two`;
DROP TABLE IF EXISTS `table_one`;
SQL;
        $adapter = Site::getAdapter();
        $adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
    }
}