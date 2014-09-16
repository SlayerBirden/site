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
        $this->treeProp->setAccessible(true);
    }

    /**
     * @test
     */
    public function testBuildDependencyTree()
    {
        $client1 = new DdlClient();
        $client1->id = 1;
        $client1->code = 'm1';
        $client1->version = '0.1.0';
        $client1->config = [
            'modules' => [
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
            ]
        ];
        $client2 = new DdlClient();
        $client2->id = 2;
        $client2->code = 'm2';
        $client2->version = '0.1.0';
        $client2->config = [
            'modules' => [
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
        ];
        $client2->dependencies = ['m1'];
        $client3 = new DdlClient();
        $client3->id = 3;
        $client3->code = 'm3';
        $client3->version = '2';
        $client3->config = [
            'modules' => [
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
                ],
            ],
        ];
        $client3->dependencies = ['m1', 'm2'];
        $this->reader->buildDependencyTree(array($client1, $client2, $client3));
        $expected = [
            'modules' => [
                'client' => 'm1',
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
                        'client' => 'm2',
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
                        'dependents' => [],
                    ],
                    [
                        'client' => 'm3',
                        'version' => '2',
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
                            ],
                        ],
                        'dependents' => [],
                    ],
                ],
            ],
            'test' => [
                'client' => 'm1',
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
                'dependents' => [],
            ],
        ];
        $tree = $this->reader->getDependencyTree();
        $this->assertEquals($expected, $tree, print_r($tree, 1));
        return $tree;
    }

    /**
     * @test
     */
    public function testRecursiveMerge()
    {
        $branch = [
            'client' => 'm1',
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
                    'client' => 'm2',
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
                            'client' => 'm3',
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
                    'client' => 'm4',
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
                            'client' => 'm5',
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
     * @depends testBuildDependencyTree
     * @depends testRecursiveMerge
     */
    public function testMergeDependencyTree($tree)
    {
        $expected = [
            'modules' => [
                'client' => 'm1',
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
            ],
            'test' => [
                'client' => 'm1',
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
            ],
        ];
        $this->treeProp->setValue($this->reader, $tree);
        $this->reader->mergeDependencyTree();
        $tree = $this->reader->getDependencyTree();
        $this->assertEquals($expected, $tree, print_r($tree, 1));
    }

    /**
     * @test
     */
    public function testDependencyBubbleSortCallback()
    {
        $client1 = new DdlClient();
        $client1->id = 1;
        $client1->code = 'm1';
        $client2 = new DdlClient();
        $client2->id = 2;
        $client2->code = 'm2';
        $client2->dependencies = ['m1'];
        $client3 = new DdlClient();
        $client3->id = 3;
        $client3->code = 'm3';
        $client3->dependencies = ['m5'];
        $client4 = new DdlClient();
        $client4->id = 4;
        $client4->code = 'm4';
        $client5 = new DdlClient();
        $client5->code = 'm5';
        $client5->dependencies = ['m1'];

        $clients = [$client1, $client2, $client3, $client4, $client5];
        $expected = [$client1, $client4, $client2, $client5, $client3];
        usort($clients, array($this->reader, 'dependencyBubbleSortCallback'));
        $this->assertEquals($expected, $clients);
    }

    /**
     * @test
     * @covers getMergedConfig
     */
    public function testGetMergedConfig()
    {
        $tree = [
            'modules' => [
                'client' => 'm1',
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
            ],
            'test' => [
                'client' => 'm1',
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
            ],
        ];

        $this->treeProp->setValue($this->reader, $tree);
        $expected = [
            'modules' => [
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
        $this->assertEquals($expected, $this->reader->getMergedConfig());
    }

}
