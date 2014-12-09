<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Test;

use Maketok\App\Site;
use Maketok\Installer\Ddl\Directives;
use Maketok\Installer\Ddl\Manager;
use Maketok\Installer\Ddl\Resource\Model\DdlClient;

class ManagerTest extends \PHPUnit_Framework_TestCase
{

    /** @var \Maketok\Installer\Ddl\Manager */
    protected static $_manager;

    public function setUp()
    {
        self::$_manager = new Manager(
            $this->getMock('Maketok\Installer\Ddl\ConfigReader'),
            $this->getMock('Maketok\Installer\Ddl\Mysql\Resource', [], [], '', false),
            new Directives(),
            null,
            Site::getSC()->get('logger'),
            Site::getSC()->get('ddl_client_table')
        );
    }

    /**
     * @test
     * @covers Maketok\Installer\Ddl\Manager::addClient
     */
    public function testAddClient()
    {
        $client = $this->getMock('Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())->method('getDdlVersion')->will($this->returnValue('0.1.0'));
        $client->expects($this->any())->method('getDdlConfig')->will($this->returnValue([]));
        $client->expects($this->any())->method('getDdlCode')->will($this->returnValue('t1'));

        self::$_manager->addClient($client);
        $this->assertTrue(self::$_manager->hasClients());
        $this->assertCount(1, self::$_manager->getClients());
        /** @var DdlClient $actual */
        $actual = current(self::$_manager->getClients());
        $this->assertEquals('0.1.0', $actual->version);
        $this->assertEquals([], $actual->config);
        $this->assertEquals('t1', $actual->code);

        $client = $this->getMock('Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())->method('getDdlVersion')->will($this->returnValue('0.1.0'));
        $client->expects($this->any())->method('getDdlConfig')->will($this->returnValue([]));
        $client->expects($this->any())->method('getDdlCode')->will($this->returnValue('t2'));

        self::$_manager->addClient($client);
        $this->assertTrue(self::$_manager->hasClients());
        $this->assertCount(2, self::$_manager->getClients());
        $clients = self::$_manager->getClients();
        $actual = $clients['t2'];
        $this->assertEquals('0.1.0', $actual->version);
        $this->assertEquals([], $actual->config);
        $this->assertEquals('t2', $actual->code);

        $client = $this->getMock('Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())->method('getDdlVersion')->will($this->returnValue('0.2.0'));
        $client->expects($this->any())->method('getDdlConfig')->will($this->returnValue(['bla']));
        $client->expects($this->any())->method('getDdlCode')->will($this->returnValue('t2'));

        self::$_manager->addClient($client);
        $this->assertTrue(self::$_manager->hasClients());
        $this->assertCount(2, self::$_manager->getClients());
        $clients = self::$_manager->getClients();
        $actual = $clients['t2'];
        $this->assertEquals('0.2.0', $actual->version);
        $this->assertEquals(['bla'], $actual->config);
        $this->assertEquals('t2', $actual->code);
    }

    /**
     * @test
     * @covers Maketok\Installer\Ddl\Manager::createDirectives
     */
    public function testCreateDirectives()
    {
        $refProp = new \ReflectionProperty(get_class(self::$_manager), '_reader');
        $refProp->setAccessible(true);

        $mock = $this->getMock('Maketok\Installer\Ddl\ConfigReader');
        $merged = [
            'modules' => [
                'columns' => [
                    'id' => [
                        'type' => 'integer',
                    ],
                    'code' => [
                        'type' => 'varchar',
                        'length' => 255,
                        'old_name' => 'alias',
                    ],
                    'version' => [
                        'type' => 'varchar',
                        'length' => 255,
                    ],
                ],
                'constraints' => [
                    'primary' => [
                        'type' => 'primaryKey',
                        'definition' => ['id'],
                    ],
                    'UNQ_KEY_CODE' => [
                        'type' => 'uniqueKey',
                        'definition' => ['code'],
                    ],
                ],
            ],
            'test' => [
                'columns' => [
                    'id' => [
                        'type' => 'integer',
                    ],
                    'code' => [
                        'type' => 'varchar'
                    ],
                ],
                'constraints' => [
                    'primary' => [
                        'type' => 'primaryKey',
                        'definition' => ['id'],
                    ],
                ],
            ],
        ];
        $mock->expects($this->any())
            ->method('getMergedConfig')
            ->will($this->returnValue($merged));
        $refProp->setValue(self::$_manager, $mock);

        $refPropRes = new \ReflectionProperty(get_class(self::$_manager), '_resource');
        $refPropRes->setAccessible(true);
        $mock = $this->getMock('Maketok\Installer\Ddl\Mysql\Resource', [], [], '', false);
        $mock->expects($this->any())
            ->method('getTable')
            ->will($this->returnValueMap([
                ['modules', [
                    'columns' => [
                        'id' => [
                            'type' => 'integer',
                        ],
                        'alias' => [
                            'type' => 'varchar',
                            'length' => 255,
                        ],
                        'title' => [
                            'type' => 'varchar',
                            'length' => 255,
                        ],
                    ],
                    'constraints' => [
                        'primary' => [
                            'type' => 'primaryKey',
                            'definition' => ['id'],
                        ],
                    ],
                ]],
                ['test', [
                    'columns' => [
                        'id' => [
                            'type' => 'integer',
                        ],
                        'code' => [
                            'type' => 'varchar'
                        ],
                    ],
                    'constraints' => [
                        'primary' => [
                            'type' => 'primaryKey',
                            'definition' => ['id'],
                        ],
                    ],
                ]],
            ]));
        $refPropRes->setValue(self::$_manager, $mock);

        self::$_manager->createDirectives();

        /** @var Directives $expectedDirectives */
        $expectedDirectives = self::$_manager->getDirectives();
        $this->assertCount(1, $expectedDirectives->addColumns);
        $this->assertCount(1, $expectedDirectives->changeColumns);
        $this->assertCount(1, $expectedDirectives->dropColumns);
        $this->assertCount(1, $expectedDirectives->addConstraints);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @covers Maketok\Installer\Ddl\Manager::createDirectives
     */
    public function testCreateDirectivesException()
    {
        $refProp = new \ReflectionProperty(get_class(self::$_manager), '_reader');
        $refProp->setAccessible(true);

        $mock = $this->getMock('Maketok\Installer\Ddl\ConfigReader');
        $mock->expects($this->any())->method('getMergedConfig')->will($this->returnValue([1]));
        $refProp->setValue(self::$_manager, $mock);
        self::$_manager->createDirectives();
    }
}
