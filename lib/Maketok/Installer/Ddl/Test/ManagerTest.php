<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl\Test;

use Maketok\Installer\Ddl\Directives;
use Maketok\Installer\Ddl\Manager;
use Maketok\Installer\Ddl\Resource\Model\DdlClient;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Maketok\Installer\Ddl\Manager */
    protected static $manager;

    public function setUp()
    {
        $tableMapper = $this->getMock('Maketok\Installer\Ddl\Resource\Model\DdlClientType', [], [], '', false);
        // simply throw exception, let's pretend there's no client available yet
        $tableMapper->expects($this->any())->method('getClientByCode')->will($this->throwException(new \Exception('')));
        self::$manager = new Manager(
            $this->getMock('Maketok\Installer\Ddl\ConfigReader'),
            $this->getMock('Maketok\Installer\Ddl\Mysql\Resource', [], [], '', false),
            new Directives(),
            null,
            $tableMapper
        );
    }

    /**
     * @test
     */
    public function testAddClient()
    {
        $client = $this->getMock('Maketok\Installer\Ddl\ClientInterface', [], [], '', false);
        $client->expects($this->any())->method('getDdlVersion')->will($this->returnValue('0.1.0'));
        $client->expects($this->any())->method('getDdlConfig')->will($this->returnValue([]));
        $client->expects($this->any())->method('getDdlCode')->will($this->returnValue('t1'));

        self::$manager->addClient($client);
        $this->assertTrue(self::$manager->hasClients());
        $this->assertCount(1, self::$manager->getClients());
        /** @var DdlClient $actual */
        $actual = current(self::$manager->getClients());
        $this->assertEquals('0.1.0', $actual->version);
        $this->assertEquals([], $actual->config);
        $this->assertEquals('t1', $actual->code);

        $client = $this->getMock('Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())->method('getDdlVersion')->will($this->returnValue('0.1.0'));
        $client->expects($this->any())->method('getDdlConfig')->will($this->returnValue([]));
        $client->expects($this->any())->method('getDdlCode')->will($this->returnValue('t2'));

        self::$manager->addClient($client);
        $this->assertTrue(self::$manager->hasClients());
        $this->assertCount(2, self::$manager->getClients());
        $clients = self::$manager->getClients();
        $actual = $clients['t2'];
        $this->assertEquals('0.1.0', $actual->version);
        $this->assertEquals([], $actual->config);
        $this->assertEquals('t2', $actual->code);

        $client = $this->getMock('Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())->method('getDdlVersion')->will($this->returnValue('0.2.0'));
        $client->expects($this->any())->method('getDdlConfig')->will($this->returnValue(['bla']));
        $client->expects($this->any())->method('getDdlCode')->will($this->returnValue('t2'));

        self::$manager->addClient($client);
        $this->assertTrue(self::$manager->hasClients());
        $this->assertCount(2, self::$manager->getClients());
        $clients = self::$manager->getClients();
        $actual = $clients['t2'];
        $this->assertEquals('0.2.0', $actual->version);
        $this->assertEquals(['bla'], $actual->config);
        $this->assertEquals('t2', $actual->code);
    }

    /**
     * @test
     */
    public function testCreateDirectives()
    {
        $refProp = new \ReflectionProperty(get_class(self::$manager), 'reader');
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
        $refProp->setValue(self::$manager, $mock);

        $refPropRes = new \ReflectionProperty(get_class(self::$manager), 'resource');
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
        $refPropRes->setValue(self::$manager, $mock);

        self::$manager->createDirectives();

        /** @var Directives $expectedDirectives */
        $expectedDirectives = self::$manager->getDirectives();
        $this->assertCount(1, $expectedDirectives->addColumns);
        $this->assertCount(1, $expectedDirectives->changeColumns);
        $this->assertCount(1, $expectedDirectives->dropColumns);
        $this->assertCount(1, $expectedDirectives->addConstraints);
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function testCreateDirectivesException()
    {
        $refProp = new \ReflectionProperty(get_class(self::$manager), 'reader');
        $refProp->setAccessible(true);

        $mock = $this->getMock('Maketok\Installer\Ddl\ConfigReader');
        $mock->expects($this->any())->method('getMergedConfig')->will($this->returnValue([1]));
        $refProp->setValue(self::$manager, $mock);
        self::$manager->createDirectives();
    }
}
