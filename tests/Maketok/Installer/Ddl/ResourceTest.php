<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Installer\Ddl;

use Maketok\Installer\Ddl\Directives;
use Maketok\Installer\Ddl\Mysql\Procedure\AddConstraint;
use Maketok\Installer\Ddl\Mysql\Resource;
use Maketok\Util\ConfigGetter;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * @var \Maketok\Installer\Ddl\Mysql\Resource
     */
    protected $resource;

    /**
     * set up tables to test
     */
    public function setUp()
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
        $getter = new ConfigGetter();
        $optionConfigs = $getter->getConfig(AR . '/config/di', ['travis.parameters', 'local.parameters', 'test.parameters']);
        $merged = [];
        foreach ($optionConfigs as $config) {
            $merged = array_replace_recursive($merged, $config);
        }
        $params = $merged['parameters'];
        $driver = [
            'driver' => 'pdo_mysql',
            'hostname' => $params['db_host'],
            'database' => $params['db_database'],
            'username' => $params['db_user'],
            'password' => $params['db_passw'],
        ];
        $this->adapter = new Adapter($driver);
        $sqlObj = new Sql($this->adapter);
        $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $this->resource = new Resource($this->adapter, $sqlObj);
    }

    /**
     * @test
     */
    public function testGetTable()
    {
        $result = $this->resource->getTable('test_store');
        $this->assertNotEmpty($result);

        $this->assertCount(8, $result['columns']);
        $this->assertCount(3, $result['indices']);
        $this->assertCount(3, $result['constraints']);
        $fk = end($result['constraints']);
        $this->assertNotEmpty($fk);
        $this->assertEquals('foreignKey', $fk['type']);
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
        $result = $this->resource->getColumn('test_store', 'group_id');
        $this->assertNotEmpty($result);

        $this->assertEquals('group_id', $result['name']);
        $this->assertEquals('smallint', $result['type']);
        $this->assertEquals('5', $result['length']);
        $this->assertFalse($result['nullable']);
        $this->assertFalse(isset($result['auto_increment']));
        $this->assertTrue($result['unsigned']);
        $this->assertEquals('0', $result['default']);

        $result = $this->resource->getColumn('test_website', 'code');
        $this->assertNotEmpty($result);

        $this->assertEquals('code', $result['name']);
        $this->assertEquals('varchar', $result['type']);
        $this->assertEquals('32', $result['length']);
        $this->assertFalse($result['nullable']);
        $this->assertFalse(isset($result['auto_increment']));
        $this->assertFalse(isset($result['unsigned']));
        $this->assertEquals('', $result['default']);

        $result = $this->resource->getColumn('test_website', 'name');
        $this->assertNotEmpty($result);

        $this->assertEquals('name', $result['name']);
        $this->assertEquals('varchar', $result['type']);
        $this->assertEquals('64', $result['length']);
        $this->assertFalse($result['nullable']);
        $this->assertFalse(isset($result['auto_increment']));
        $this->assertFalse(isset($result['unsigned']));
        $this->assertEquals('oleg', $result['default']);

        $result = $this->resource->getColumn('test_website', 'created_at');
        $this->assertNotEmpty($result);

        $this->assertEquals('created_at', $result['name']);
        $this->assertEquals('timestamp', $result['type']);
        $this->assertFalse(isset($result['length']));
        $this->assertFalse(isset($result['unsigned']));
        $this->assertFalse($result['nullable']);
        $this->assertEquals('CURRENT_TIMESTAMP', $result['default']);

        $result = $this->resource->getColumn('test_store', 'updated_at');
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
        $result = $this->resource->getConstraint('test_website', 'PRIMARY');
        $this->assertNotEmpty($result);

        $this->assertEquals('primaryKey', $result['type']);
        $this->assertEquals('primary', $result['name']);
        $this->assertEquals('website_id', $result['definition']);

        $result = $this->resource->getConstraint('test_website', 'code');
        $this->assertNotEmpty($result);

        $this->assertEquals('uniqueKey', $result['type']);
        $this->assertEquals('code', $result['name']);
        $this->assertEquals('code', $result['definition']);

        $result = $this->resource->getConstraint('test_store', 'FK_STORE_WEBSITE');
        $this->assertNotEmpty($result);

        $this->assertEquals('foreignKey', $result['type']);
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
        $result = $this->resource->getIndex('test_store', 'is_active');
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
        $this->assertEquals([], $this->resource->getTable($falseTable));
        $this->assertEquals([], $this->resource->getColumn($falseTable, $falseColumn));
        $this->assertEquals([],
            $this->resource->getConstraint($falseTable, $falseConstraint));
        $this->assertEquals([], $this->resource->getIndex($falseTable, $falseIndex));
    }

    /**
     * @test
     */
    public function testCreateProcedures()
    {
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
                            'type' => 'varchar',
                            'length' => 255
                        ],
                        'area' => [
                            'type' => 'varchar',
                            'length' => 55,
                            'nullable' => true
                        ],
                    ],
                ]
            ]
        ];
        $directives->addConstraints = [
            [
                'test',
                'primary',
                [
                    'type' => 'primaryKey',
                    'definition' => 'id',
                ]
            ],
            [
                'test',
                'UNQ_KEY_CODE_AREA',
                [
                    'type' => 'uniqueKey',
                    'definition' => ['code', 'area'],
                ]
            ],
            [
                'test',
                'FK_CODE_PARENT_CODE',
                [
                    'type' => 'foreignKey',
                    'column' => 'parent_id',
                    'reference_table' => 'modules',
                    'reference_column' => 'reference_id',
                    'on_delete' => 'CASCADE',
                    'on_update' => 'CASCADE',
                ]
            ],
        ];
        $directives->changeColumns = [
            [
                'test2',
                'oldCol',
                'newCol',
                ['type' => 'integer']
            ],
        ];
        $directives->dropFks = [
            [
                'test2',
                'FK_SOME',
                'foreignKey'
            ],
        ];
        $directives->dropPks = [
            [
                'test2',
                'primary',
                'primaryKey'
            ],
        ];
        $directives->dropColumns = [
            [
                'test',
                'code',
            ],
        ];
        $directives->addIndices = [
            [
                'test',
                'KEY_IDX',
                [
                    'type' => 'index',
                    'definition' => 'some_column',
                ]
            ],
        ];
        $directives->dropIndices = [
            [
                'test',
                'KEY_IDX',
                'index',
            ],
            [
                'test2',
                'UNQ_KEY_OOPS',
                'uniqueKey'
            ],
        ];
        $directives->dropTables = [
            [
                'test3',
            ],
        ];

        $this->resource->createProcedures($directives);
        $refProp = new \ReflectionProperty(get_class($this->resource), 'procedures');
        $refProp->setAccessible(true);
        // the order is switched
        $expected = [
            "DROP TABLE `test3`",
            "CREATE TABLE `test` ( `id` INTEGER NOT NULL, `code` VARCHAR(255) NOT NULL, `area` VARCHAR(55) )",
            "ALTER TABLE `test2` CHANGE COLUMN `oldCol` `newCol` INTEGER NOT NULL, DROP PRIMARY KEY, DROP FOREIGN KEY `FK_SOME`, DROP INDEX `UNQ_KEY_OOPS`",
            "ALTER TABLE `test` DROP COLUMN `code`, ADD CONSTRAINT `id` PRIMARY KEY (`id`), ADD CONSTRAINT `UNQ_KEY_CODE_AREA` UNIQUE (`code`, `area`), ADD CONSTRAINT `FK_CODE_PARENT_CODE` FOREIGN KEY (`parent_id`) REFERENCES `modules` (`reference_id`) ON DELETE CASCADE ON UPDATE CASCADE, ADD INDEX `KEY_IDX`(`some_column`), DROP INDEX `KEY_IDX`",
        ];
        $procedures = $refProp->getValue($this->resource);
        reset($procedures);
        for ($i = 0; $i < count($expected); ++$i) {
            $this->assertEquals($expected[$i], preg_replace('/\s+/', ' ', current($procedures)));
            next($procedures);
        }
    }

    /**
     * @test
     */
    public function testCreateProceduresTimestamp()
    {
        $directives = new Directives();
        $directives->addColumns = [
            [
                'test2',
                'oldCol',
                ['type' => 'timestamp', 'on_update' => 1]
            ],
        ];

        $this->resource->createProcedures($directives);
        $refProp = new \ReflectionProperty(get_class($this->resource), 'procedures');
        $refProp->setAccessible(true);
        // the order is switched
        $expected = [
            "ALTER TABLE `test2` ADD COLUMN `oldCol` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP",
        ];
        $procedures = $refProp->getValue($this->resource);
        $this->assertCount(1, $procedures);
        $this->assertEquals($expected[0], preg_replace('/\s+/', ' ', current($procedures)));
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Wrong context of launching create procedures method
     */
    public function testCreateProceduresWrongContext()
    {
        $directives = new Directives();
        $this->resource->createProcedures($directives);
        $this->resource->createProcedures($directives);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not enough parameter to change column
     */
    public function testCreateProceduresWrongDirectives()
    {
        $directives = new Directives();
        $directives->changeColumns = [
            [
                'test',
                [],
            ],
        ];
        $this->resource->createProcedures($directives);
    }

    /**
     * Multiple Columns FK related tests
     * @test
     */
    public function testMultipleColumnFK()
    {
        $deleteSql = $sql = <<<'SQL'
DROP TABLE IF EXISTS `test_t2`;
DROP TABLE IF EXISTS `test_t1`;
SQL;
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS `test_t1` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `area` varchar(32) NOT NULL DEFAULT 'base',
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`, `area`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='t1';

CREATE TABLE IF NOT EXISTS `test_t2` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` smallint(5) unsigned DEFAULT '0',
  `area` varchar(32) NOT NULL DEFAULT 'base',
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_STORE_T1` FOREIGN KEY (`parent_id`, `area`)
   REFERENCES `test_t1` (`id`, `area`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='t2';
SQL;
        $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);

        $pk = $this->resource->getConstraint('test_t1', 'primary');
        $this->assertNotEmpty($pk);
        $this->assertEquals(['id', 'area'], $pk['definition']);

        $result = $this->resource->getConstraint('test_t2', 'FK_STORE_T1');
        $this->assertNotEmpty($result);

        $this->assertEquals(['parent_id', 'area'], $result['column']);
        $this->assertEquals(['id', 'area'], $result['reference_column']);

        $this->adapter->query($deleteSql, Adapter::QUERY_MODE_EXECUTE);

        //=============================================== test auto index creation
        // assert THAT index IS added when none exists
        $config1 = [
            'modules' => [
                'constraints' => [
                    'FK_KEY_TEST' => [
                        'type' => 'foreignKey',
                        'column' => ['parent_id', 'website'],
                        'reference_table' => 'test_parent',
                        'reference_column' => ['id', 'website'],
                    ]
                ],
            ],
        ];
        $this->resource->processValidateMergedConfig($config1);
        $this->assertSame([
            'modules' => [
                'constraints' => [
                    'FK_KEY_TEST' => [
                        'type' => 'foreignKey',
                        'column' => ['parent_id', 'website'],
                        'reference_table' => 'test_parent',
                        'reference_column' => ['id', 'website'],
                        'on_update' => AddConstraint::DEFAULT_ON_UPDATE,
                        'on_delete' => AddConstraint::DEFAULT_ON_DELETE,
                    ]
                ],
                'indices' => [
                    'FK_KEY_TEST' => [
                        'type' => 'index',
                        'definition' => ['parent_id', 'website']
                    ],
                ]
            ],
        ], $config1);
        //============================ procedure test
        $directives = new Directives();
        $directives->addConstraints = [
            [
                'test',
                'FK_CODE_PARENT_CODE',
                [
                    'type' => 'foreignKey',
                    'column' => ['parent_id', 'website'],
                    'reference_table' => 'modules',
                    'reference_column' => ['reference_id', 'website'],
                    'on_delete' => 'CASCADE',
                    'on_update' => 'CASCADE',
                ]
            ],
        ];

        $this->resource->createProcedures($directives);
        $refProp = new \ReflectionProperty(get_class($this->resource), 'procedures');
        $refProp->setAccessible(true);
        // the order is switched
        $expected = [
            "ALTER TABLE `test` ADD CONSTRAINT `FK_CODE_PARENT_CODE` FOREIGN KEY (`parent_id`, `website`) REFERENCES `modules` (`reference_id`, `website`) ON DELETE CASCADE ON UPDATE CASCADE",
        ];
        $procedures = $refProp->getValue($this->resource);
        $this->assertEquals($expected[0], preg_replace('/\s+/', ' ', current($procedures)));
    }

    /**
     * @test
     */
    public function processValidateMergedConfig()
    {
        // validate that INDEX for FK is NOT added if one exists already
        $config1 = [
            'modules' => [
                'constraints' => [
                    'primary' => [
                        'type' => 'primaryKey',
                        'definition' => 'id',
                    ],
                    'UNQ_KEY_CODE' => [
                        'type' => 'uniqueKey',
                        'definition' => 'code',
                    ],
                    'FK_KEY_TEST' => [
                        'type' => 'foreignKey',
                        'column' => 'id',
                        'reference_table' => 'test_parent',
                        'reference_column' => 'id',
                    ]
                ],
            ],
        ];
        $this->resource->processValidateMergedConfig($config1);
        $this->assertSame([
            'modules' => [
                'constraints' => [
                    'primary' => [
                        'type' => 'primaryKey',
                        'definition' => 'id',
                    ],
                    'UNQ_KEY_CODE' => [
                        'type' => 'uniqueKey',
                        'definition' => 'code',
                    ],
                    'FK_KEY_TEST' => [
                        'type' => 'foreignKey',
                        'column' => 'id',
                        'reference_table' => 'test_parent',
                        'reference_column' => 'id',
                        'on_update' => AddConstraint::DEFAULT_ON_UPDATE,
                        'on_delete' => AddConstraint::DEFAULT_ON_DELETE,
                    ]
                ],
            ],
        ], $config1);

        // assert THAT index IS added when none exists
        $config1 = [
            'modules' => [
                'constraints' => [
                    'primary' => [
                        'type' => 'primaryKey',
                        'definition' => 'id',
                    ],
                    'UNQ_KEY_CODE' => [
                        'type' => 'uniqueKey',
                        'definition' => 'code',
                    ],
                    'FK_KEY_TEST' => [
                        'type' => 'foreignKey',
                        'column' => 'parent_id',
                        'reference_table' => 'test_parent',
                        'reference_column' => 'id',
                    ]
                ],
                'indices' => [
                    'KEY_TEST' => [
                        'type' => 'index',
                        'definition' => 'date'
                    ]
                ]
            ],
        ];
        $this->resource->processValidateMergedConfig($config1);
        $this->assertSame([
            'modules' => [
                'constraints' => [
                    'primary' => [
                        'type' => 'primaryKey',
                        'definition' => 'id',
                    ],
                    'UNQ_KEY_CODE' => [
                        'type' => 'uniqueKey',
                        'definition' => 'code',
                    ],
                    'FK_KEY_TEST' => [
                        'type' => 'foreignKey',
                        'column' => 'parent_id',
                        'reference_table' => 'test_parent',
                        'reference_column' => 'id',
                        'on_update' => AddConstraint::DEFAULT_ON_UPDATE,
                        'on_delete' => AddConstraint::DEFAULT_ON_DELETE,
                    ]
                ],
                'indices' => [
                    'KEY_TEST' => [
                        'type' => 'index',
                        'definition' => 'date'
                    ],
                    'FK_KEY_TEST' => [
                        'type' => 'index',
                        'definition' => 'parent_id'
                    ],
                ]
            ],
        ], $config1);
    }

    /**
     * tear down routine
     */
    public function tearDown()
    {
        $sql = <<<'SQL'
DROP TABLE IF EXISTS `test_store`;
DROP TABLE IF EXISTS `test_website`;
SQL;
        $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
    }
}
