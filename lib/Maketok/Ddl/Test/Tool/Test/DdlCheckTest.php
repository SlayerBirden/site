<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Ddl\Test\Tool\Test;

use Maketok\Ddl\Test\Tool\DdlCheck;
use Maketok\App\Site;
use Zend\Db\Adapter\Adapter;

class DdlCheckTest extends \PHPUnit_Framework_TestCase
{

    /**
     * set up tables to test
     */
    public static function setUpBeforeClass()
    {
        $sql = <<<'SQL'
CREATE TABLE `test_website` (
  `website_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(64) NOT NULL DEFAULT 'oleg',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `default_group_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_default` tinyint(1) unsigned DEFAULT '0',
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
  PRIMARY KEY (`store_id`),
  UNIQUE KEY `code` (`code`),
  KEY `FK_STORE_WEBSITE` (`website_id`),
  KEY `is_active` (`is_active`,`sort_order`),
  KEY `FK_STORE_GROUP` (`group_id`),
  CONSTRAINT `FK_STORE_WEBSITE` FOREIGN KEY (`website_id`) REFERENCES `test_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Stores';
SQL;
        $adapter = Site::getAdapter();
        $adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
    }

    public function testCheckTable()
    {
        $ddlCheck = new DdlCheck();
        $result = $ddlCheck->checkTable('test_store');
        $this->assertNotEmpty($result);

        $this->assertCount(7, $result['columns']);
        $this->assertCount(3, $result['indexes']);
        $this->assertCount(3, $result['constraints']);
        $fk = $result['constraints'][2];
        $this->assertNotEmpty($fk);
        $this->assertEquals('foreign_key', $fk['type']);
        $this->assertEquals('FK_STORE_WEBSITE', $fk['name']);
        $this->assertEquals('website_id', $fk['column']);
        $this->assertEquals('test_website', $fk['reference_table']);
        $this->assertEquals('website_id', $fk['reference_column']);
        $this->assertEquals('CASCADE', $fk['on_delete']);
        $this->assertEquals('CASCADE', $fk['on_update']);
    }

    public function testCheckColumn()
    {
        $ddlCheck = new DdlCheck();
        $result = $ddlCheck->checkColumn('test_store', 'group_id');
        $this->assertNotEmpty($result);

        $this->assertEquals('group_id', $result['name']);
        $this->assertEquals('smallint', $result['type']);
        $this->assertEquals('5', $result['length']);
        $this->assertFalse($result['nullable']);
        $this->assertFalse(isset($result['auto_increment']));
        $this->assertTrue($result['unsigned']);
        $this->assertEquals('0', $result['default']);

        $result = $ddlCheck->checkColumn('test_website', 'code');
        $this->assertNotEmpty($result);

        $this->assertEquals('code', $result['name']);
        $this->assertEquals('varchar', $result['type']);
        $this->assertEquals('32', $result['length']);
        $this->assertFalse($result['nullable']);
        $this->assertFalse(isset($result['auto_increment']));
        $this->assertFalse(isset($result['unsigned']));
        $this->assertEquals('', $result['default']);

        $result = $ddlCheck->checkColumn('test_website', 'name');
        $this->assertNotEmpty($result);

        $this->assertEquals('name', $result['name']);
        $this->assertEquals('varchar', $result['type']);
        $this->assertEquals('64', $result['length']);
        $this->assertFalse($result['nullable']);
        $this->assertFalse(isset($result['auto_increment']));
        $this->assertFalse(isset($result['unsigned']));
        $this->assertEquals('oleg', $result['default']);

    }

    public function testCheckIndex()
    {
        $ddlCheck = new DdlCheck();
        $result = $ddlCheck->checkIndex('test_store', 'is_active');
        $this->assertNotEmpty($result);

        $this->assertEquals('is_active', $result['name']);
        $this->assertEquals(array('is_active', 'sort_order'), $result['definition']);
    }

    static public function tearDownAfterClass()
    {
        $sql = <<<'SQL'
DROP table `test_store`;
DROP table `test_website`;
SQL;
        $adapter = Site::getAdapter();
        $adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
    }
}
