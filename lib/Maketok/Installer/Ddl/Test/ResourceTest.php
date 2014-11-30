<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Test;

use Maketok\App\Site;
use Maketok\Installer\Ddl\Directives;
use Maketok\Installer\Ddl\Mysql\Resource;
use Zend\Db\Adapter\Adapter;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Maketok\Installer\Ddl\Mysql\Resource */
    protected static $_resource;

    /**
     * set up tables to test
     */
    public static function setUpBeforeClass()
    {
        $sql = <<<'SQL'
DROP TABLE IF EXISTS `test_store`;
DROP TABLE IF EXISTS `test_website`;
CREATE TABLE `test_website` (
  `website_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(64) NOT NULL DEFAULT 'oleg',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `default_group_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_default` tinyint(1) unsigned DEFAULT '0',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`website_id`),
  UNIQUE KEY `code` (`code`),
  KEY `sort_order` (`sort_order`),
  KEY `default_group_id` (`default_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Websites';

CREATE TABLE `test_store` (
  `store_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL DEFAULT '',
  `website_id` smallint(5) unsigned DEFAULT '0',
  `group_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`store_id`),
  UNIQUE KEY `code` (`code`),
  KEY `FK_STORE_WEBSITE` (`website_id`),
  KEY `is_active` (`is_active`,`sort_order`),
  KEY `FK_STORE_GROUP` (`group_id`),
  CONSTRAINT `FK_STORE_WEBSITE` FOREIGN KEY (`website_id`)
   REFERENCES `test_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Stores';
SQL;
        $adapter = Site::getServiceContainer()->get('adapter');
        $adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
        self::$_resource = new Resource($adapter,
            Site::getServiceContainer()->get('zend_db_sql'));
    }

    /**
     * @test
     */
    public function testGetTable()
    {
        $result = self::$_resource->getTable('test_store');
        $this->assertNotEmpty($result);

        $this->assertCount(8, $result['columns']);
        $this->assertCount(3, $result['indices']);
        $this->assertCount(3, $result['constraints']);
        $fk = end($result['constraints']);
        $this->assertNotEmpty($fk);
        $this->assertEquals('foreign_key', $fk['type']);
        $this->assertEquals('FK_STORE_WEBSITE', $fk['name']);
        $this->assertEquals('website_id', $fk['column']);
        $this->assertEquals('test_website', $fk['reference_table']);
        $this->assertEquals('website_id', $fk['reference_column']);
        $this->assertEquals('CASCADE', $fk['on_delete']);
        $this->assertEquals('CASCADE', $fk['on_update']);
    }

    /**
     * @test
     */
    public function testGetColumn()
    {
        $result = self::$_resource->getColumn('test_store', 'group_id');
        $this->assertNotEmpty($result);

        $this->assertEquals('group_id', $result['name']);
        $this->assertEquals('smallint', $result['type']);
        $this->assertEquals('5', $result['length']);
        $this->assertFalse($result['nullable']);
        $this->assertFalse(isset($result['auto_increment']));
        $this->assertTrue($result['unsigned']);
        $this->assertEquals('0', $result['default']);

        $result = self::$_resource->getColumn('test_website', 'code');
        $this->assertNotEmpty($result);

        $this->assertEquals('code', $result['name']);
        $this->assertEquals('varchar', $result['type']);
        $this->assertEquals('32', $result['length']);
        $this->assertFalse($result['nullable']);
        $this->assertFalse(isset($result['auto_increment']));
        $this->assertFalse(isset($result['unsigned']));
        $this->assertEquals('', $result['default']);

        $result = self::$_resource->getColumn('test_website', 'name');
        $this->assertNotEmpty($result);

        $this->assertEquals('name', $result['name']);
        $this->assertEquals('varchar', $result['type']);
        $this->assertEquals('64', $result['length']);
        $this->assertFalse($result['nullable']);
        $this->assertFalse(isset($result['auto_increment']));
        $this->assertFalse(isset($result['unsigned']));
        $this->assertEquals('oleg', $result['default']);

        $result = self::$_resource->getColumn('test_website', 'created_at');
        $this->assertNotEmpty($result);

        $this->assertEquals('created_at', $result['name']);
        $this->assertEquals('timestamp', $result['type']);
        $this->assertFalse(isset($result['length']));
        $this->assertFalse(isset($result['unsigned']));
        $this->assertFalse($result['nullable']);
        $this->assertEquals('CURRENT_TIMESTAMP', $result['default']);

        $result = self::$_resource->getColumn('test_store', 'updated_at');
        $this->assertNotEmpty($result);

        $this->assertEquals('updated_at', $result['name']);
        $this->assertEquals('timestamp', $result['type']);
        $this->assertFalse(isset($result['length']));
        $this->assertFalse(isset($result['unsigned']));
        $this->assertFalse(isset($result['default']));
        $this->assertTrue($result['nullable']);
        $this->assertTrue(isset($result['on_update']));
    }

    /**
     * @test
     */
    public function testGetConstraint()
    {
        $result = self::$_resource->getConstraint('test_website', 'PRIMARY');
        $this->assertNotEmpty($result);

        $this->assertEquals('primary', $result['type']);
        $this->assertFalse(isset($result['name']));
        $this->assertEquals(array('website_id'), $result['definition']);

        $result = self::$_resource->getConstraint('test_website', 'code');
        $this->assertNotEmpty($result);

        $this->assertEquals('unique', $result['type']);
        $this->assertEquals('code', $result['name']);
        $this->assertEquals(array('code'), $result['definition']);

        $result = self::$_resource->getConstraint('test_store', 'FK_STORE_WEBSITE');
        $this->assertNotEmpty($result);

        $this->assertEquals('foreign_key', $result['type']);
        $this->assertEquals('FK_STORE_WEBSITE', $result['name']);
        $this->assertEquals('website_id', $result['column']);
        $this->assertEquals('test_website', $result['reference_table']);
        $this->assertEquals('website_id', $result['reference_column']);
        $this->assertEquals('CASCADE', $result['on_delete']);
        $this->assertEquals('CASCADE', $result['on_update']);
        $this->assertFalse(isset($result['definition']));
    }

    /**
     * @test
     */
    public function testGetIndex()
    {
        $result = self::$_resource->getIndex('test_store', 'is_active');
        $this->assertNotEmpty($result);

        $this->assertEquals('is_active', $result['name']);
        $this->assertEquals(array('is_active', 'sort_order'), $result['definition']);
    }

    /**
     * test that no error thrown on non existing table
     * and default return is array
     * @test
     */
    public function testFalseStates()
    {
        $falseTable = 'falseT';
        $falseColumn = 'fc';
        $falseConstraint = 'fcon';
        $falseIndex = 'fidx';
        $this->assertEquals([], self::$_resource->getTable($falseTable));
        $this->assertEquals([], self::$_resource->getColumn($falseTable, $falseColumn));
        $this->assertEquals([],
            self::$_resource->getConstraint($falseTable, $falseConstraint));
        $this->assertEquals([], self::$_resource->getIndex($falseTable, $falseIndex));
    }

    /**
     * @test
     * @covers createProcedures
     */
    public function testCreateProcedures()
    {
        self::$_resource = new Resource(Site::getServiceContainer()->get('adapter'),
            Site::getServiceContainer()->get('zend_db_sql'));
        $directives = new Directives();
        $directives->addTables = [
            [
                'test',
                [
                    'columns' => [
                        'id' => [
                            'type' => 'integer',
                        ],
                        'code' => [
                            'type' => 'varchar'
                        ],
                    ],
                ]
            ]
        ];
        $directives->changeColumns = [
            [
                'test2',
                'oldCol',
                'newCol',
                ['type' => 'integer']
            ],
        ];
        $directives->dropConstraints = [
            [
                'test2',
                'UNQ_KEY_OOPS',
                'unique'
            ],
        ];

        self::$_resource->createProcedures($directives);
        $refProp = new \ReflectionProperty(get_class(self::$_resource), '_procedures');
        $refProp->setAccessible(true);
        // the order is switched
        $expected = [
            "CREATE TABLE `test` ( `id` INTEGER NOT NULL, `code` VARCHAR NOT NULL )",
            "ALTER TABLE `test2` DROP INDEX `UNQ_KEY_OOPS`",
            "ALTER TABLE `test2` CHANGE COLUMN `oldCol` `newCol` INTEGER NOT NULL",
        ];
        $procedures = $refProp->getValue(self::$_resource);
        $this->assertCount(3, $procedures);
        for ($i = 0; $i < 3; ++$i) {
            $this->assertEquals($expected[$i], preg_replace('/\s+/', ' ', $procedures[$i]));
        }
    }

    /**
     * @test
     * @covers createProcedures
     */
    public function testCreateProceduresTimestamp()
    {
        self::$_resource = new Resource(Site::getServiceContainer()->get('adapter'),
            Site::getServiceContainer()->get('zend_db_sql'));
        $directives = new Directives();
        $directives->addColumns = [
            [
                'test2',
                'oldCol',
                ['type' => 'timestamp', 'on_update' => 1]
            ],
        ];

        self::$_resource->createProcedures($directives);
        $refProp = new \ReflectionProperty(get_class(self::$_resource), '_procedures');
        $refProp->setAccessible(true);
        // the order is switched
        $expected = [
            "ALTER TABLE `test2` ADD COLUMN `oldCol` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP",
        ];
        $procedures = $refProp->getValue(self::$_resource);
        $this->assertCount(1, $procedures);
        $this->assertEquals($expected[0], preg_replace('/\s+/', ' ', $procedures[0]));
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Wrong context of launching create procedures method
     */
    public function testCreateProceduresWrongContext()
    {
        self::$_resource = new Resource(Site::getServiceContainer()->get('adapter'),
            Site::getServiceContainer()->get('zend_db_sql'));
        $directives = new Directives();
        self::$_resource->createProcedures($directives);
        self::$_resource->createProcedures($directives);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not enough parameter to change column
     */
    public function testCreateProceduresWrongDirectives()
    {
        self::$_resource = new Resource(Site::getServiceContainer()->get('adapter'),
            Site::getServiceContainer()->get('zend_db_sql'));
        $directives = new Directives();
        $directives->changeColumns = [
            [
                'test',
                [],
            ],
        ];
        self::$_resource->createProcedures($directives);
    }

    /**
     * tear down routine
     */
    static public function tearDownAfterClass()
    {
        $sql = <<<'SQL'
DROP TABLE IF EXISTS `test_store`;
DROP TABLE IF EXISTS `test_website`;
SQL;
        $adapter = Site::getServiceContainer()->get('adapter');
        $adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
    }
}
