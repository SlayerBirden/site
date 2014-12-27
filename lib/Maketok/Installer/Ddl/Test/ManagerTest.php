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
    protected $manager;

    public function setUp()
    {
        $tableMapper = $this->getMock('Maketok\Installer\Ddl\Resource\Model\DdlClientType', [], [], '', false);
        // simply throw exception, let's pretend there's no client available yet
        $tableMapper->expects($this->any())->method('getClientByCode')->will($this->throwException(new \Exception('')));
        $this->manager = new Manager(
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

        $this->manager->addClient($client);
        $this->assertTrue($this->manager->hasClients());
        $this->assertCount(1, $this->manager->getClients());
        /** @var DdlClient $actual */
        $actual = current($this->manager->getClients());
        $this->assertEquals('0.1.0', $actual->version);
        $this->assertEquals([], $actual->config);
        $this->assertEquals('t1', $actual->code);

        $client = $this->getMock('Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())->method('getDdlVersion')->will($this->returnValue('0.1.0'));
        $client->expects($this->any())->method('getDdlConfig')->will($this->returnValue([]));
        $client->expects($this->any())->method('getDdlCode')->will($this->returnValue('t2'));

        $this->manager->addClient($client);
        $this->assertTrue($this->manager->hasClients());
        $this->assertCount(2, $this->manager->getClients());
        $clients = $this->manager->getClients();
        $actual = $clients['t2'];
        $this->assertEquals('0.1.0', $actual->version);
        $this->assertEquals([], $actual->config);
        $this->assertEquals('t2', $actual->code);

        $client = $this->getMock('Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())->method('getDdlVersion')->will($this->returnValue('0.2.0'));
        $client->expects($this->any())->method('getDdlConfig')->will($this->returnValue(['bla']));
        $client->expects($this->any())->method('getDdlCode')->will($this->returnValue('t2'));

        $this->manager->addClient($client);
        $this->assertTrue($this->manager->hasClients());
        $this->assertCount(2, $this->manager->getClients());
        $clients = $this->manager->getClients();
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
        $refProp = new \ReflectionProperty(get_class($this->manager), 'reader');
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
        $refProp->setValue($this->manager, $mock);

        $refPropRes = new \ReflectionProperty(get_class($this->manager), 'resource');
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
        $refPropRes->setValue($this->manager, $mock);

        $this->manager->createDirectives();

        /** @var Directives $expectedDirectives */
        $expectedDirectives = $this->manager->getDirectives();
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
        $refProp = new \ReflectionProperty(get_class($this->manager), 'reader');
        $refProp->setAccessible(true);

        $mock = $this->getMock('Maketok\Installer\Ddl\ConfigReader');
        $mock->expects($this->any())->method('getMergedConfig')->will($this->returnValue([1]));
        $refProp->setValue($this->manager, $mock);
        $this->manager->createDirectives();
    }
}
