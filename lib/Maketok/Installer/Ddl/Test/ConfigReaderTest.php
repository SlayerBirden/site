<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Test;

use Maketok\Installer\Ddl\ConfigReader;
use Maketok\Installer\Resource\Model\DdlClient;

class ConfigReaderTest extends \PHPUnit_Framework_TestCase
{

    /** @var ConfigReader */
    public $reader;
    /** @var \ReflectionProperty */
    public $treeProp;

    public function setUp()
    {
        $this->reader = new ConfigReader();
        $this->treeProp = new \ReflectionProperty(get_class($this->reader), '_tree');
    }

    /**
     * @test
     */
    public function testBuildDependencyTree()
    {
        // TODO: Implement
    }

    /**
     * @test
     */
    public function testMergeDependencyTree()
    {
        // TODO: Implement mergeDependencyTree() method.
    }

    /**
     * @test
     */
    public function testGetDependencyTree()
    {
        // TODO: Implement getDependencyTree() method.
    }

    /**
     * @test
     */
    public function testRecursiveMerge()
    {
        $branch = [
            'client' => 1,
            'version' => '0.1.0',
            'definition' => [
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
            'dependents' => [
                [
                    'client' => 2,
                    'version' => '0.1.0',
                    'definition' => [
                        'columns' => [
                            'id' => [
                                'type' => 'integer',
                            ],
                            'code' => [
                                'type' => 'varchar',
                            ],
                            'version' => [
                                'type' => 'varchar',
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
                    'dependents' => [
                        [
                            'client' => 3,
                            'version' => '0.1.0',
                            'definition' => [
                                'columns' => [
                                    'id' => [
                                        'type' => 'integer',
                                    ],
                                    'code' => [
                                        'type' => 'varchar',
                                        'length' => 255,
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
                            'dependents' => [],
                        ],
                    ],
                ],
                [
                    'client' => 4,
                    'version' => '0.1.0',
                    'definition' => [
                        'columns' => [
                            'id' => [
                                'type' => 'integer',
                            ],
                            'code' => [
                                'type' => 'varchar',
                                'length' => 155,
                            ],
                        ],
                        'constraints' => [
                            'primary' => [
                                'type' => 'primaryKey',
                                'definition' => ['id'],
                            ],
                        ],
                    ],
                    'dependents' => [
                        [
                            'client' => 5,
                            'version' => '0.1.0',
                            'definition' => [
                                'columns' => [
                                    'id' => [
                                        'type' => 'integer',
                                    ],
                                    'code' => [
                                        'type' => 'varchar',
                                        'length' => 155,
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
                            ],
                            'dependents' => [],
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'columns' => [
                'id' => [
                    'type' => 'integer',
                ],
                'code' => [
                    'type' => 'varchar',
                    'length' => 155,
                ],
                'version' => [
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
                'UNQ_KEY_CODE' => [
                    'type' => 'uniqueKey',
                    'definition' => ['code'],
                ],
            ],
        ];

        $this->assertEquals($expected, $this->reader->recursiveMerge($branch));
    }

    /**
     * @test
     */
    public function testDependencyBubbleSortCallback()
    {
        $client1 = new DdlClient();
        $client1->id = 1;
        $client2 = new DdlClient();
        $client2->id = 2;
        $client2->dependencies = [1];
        $client3 = new DdlClient();
        $client3->id = 3;
        $client3->dependencies = [5];
        $client4 = new DdlClient();
        $client4->id = 4;
        $client5 = new DdlClient();
        $client5->id = 5;
        $client5->dependencies = [1];

        $clients = [$client1, $client2, $client3, $client4, $client5];
        $expected = [$client1, $client4, $client2, $client5, $client3];
        usort($clients, array($this->reader, 'dependencyBubbleSortCallback'));
        $this->assertEquals($expected, $clients);
    }

}
