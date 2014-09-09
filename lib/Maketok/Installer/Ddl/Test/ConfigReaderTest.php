<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Test;

use Maketok\Installer\Ddl\ConfigReader;

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
    public function testValidateDependencyTree()
    {
        // TODO: Implement validateDependencyTree() method.
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
    public function testGetNotEqualsComparison()
    {
        $this->assertEquals(1, $this->reader->getNotEqualsComparison(1, '1'));
        $this->assertEquals(-1, $this->reader->getNotEqualsComparison('1', 1));
        $this->assertEquals(0, $this->reader->getNotEqualsComparison('1', '2'));
    }

    /**
     * @test
     * @depends testGetNotEqualsComparison
     */
    public function testRecursiveArrayCollide()
    {
        $compares = [
            [
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
            [
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
            [
                'columns' => [
                    'id' => [
                        'type' => 'varchar',
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
                        'definition' => ['code', 'version'],
                    ],
                ],
            ],
            [
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
            [
                'columns' => [
                    'id' => [
                        'type' => 'integer',
                    ],
                    'code' => [
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

        $expected = [
            'columns' => [
                'id' => [
                    'type' => 'integer',
                    '~type' => 'varchar',
                ],
                'code' => [
                    'length' => 155,
                    '~length' => 255,
                ],
            ],
            'constraints' => [
                'UNQ_KEY_CODE' => [
                    'definition' => ['code'],
                    '~definition' => ['code', 'version'],
                ],
            ],
        ];

        $this->assertEquals($expected,
            $this->reader->recursiveArrayCollide($compares,
                array($this->reader, 'getNotEqualsComparison')));
    }

}
