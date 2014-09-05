<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Test;

use Maketok\App\Site;
use Maketok\Installer\Ddl\ConfigReader;

class ConfigReaderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testProcessConfig()
    {
        /** @var ConfigReader $reader */
        $reader = Site::getServiceContainer()->get('installer_ddl_reader');
        $chain = [
            [
                '*test1' => [
                    'columns' => [
                        '&id' => [
                            'type' => 'integer',
                        ],
                        'code' => [
                            'type' => 'varchar',
                            'length' => '255',
                        ]
                    ],
                ],
                'test2' => [
                    'columns' => [
                        'title' => [
                            'type' => 'integer',
                            'length' => 12,
                        ],
                    ],
                    'constraints' => [
                        '~primary' => [
                            'type' => 'primaryKey',
                            'def' => 'id',
                        ]
                    ],
                ],
            ],
            [
                'test1' => [
                    'columns' => [
                        '&id' => [
                            'type' => 'integer',
                        ],
                        'code' => [
                            'type' => 'varchar',
                            'length' => '55',
                        ]
                    ],
                ],
                '~test2' => [
                    'columns' => [
                        'title' => [
                            'type' => 'integer',
                            'length' => 12,
                        ],
                    ],
                    'constraints' => [
                        'primary' => [
                            'type' => 'primaryKey',
                            'def' => 'id',
                        ]
                    ],
                ],
            ],
        ];

        $expected = [
            'tables' => [
                'add' => [
                    'test1' => [
                        'columns' => [
                            '&id' => [
                                'type' => 'integer',
                            ],
                            'code' => [
                                'type' => 'varchar',
                                'length' => '255',
                            ]
                        ],
                    ],
                ],
                'remove' => [
                    'test2' => [
                        'columns' => [
                            'title' => [
                                'type' => 'integer',
                                'length' => 12,
                            ],
                        ],
                        'constraints' => [
                            'primary' => [
                                'type' => 'primaryKey',
                                'def' => 'id',
                            ]
                        ],
                    ],
                ],
                'update' => [
                    'test2' => [
                        'columns' => [
                            'update' => [
                                'title' => [
                                    'type' => 'integer',
                                    'length' => 12,
                                ],
                            ],
                        ],
                        'constraints' => [
                            'remove' => [
                                'primary' => [
                                    'type' => 'primaryKey',
                                    'def' => 'id',
                                ],
                            ],
                        ],
                    ],
                    'test1' => [
                        'columns' => [
                            'update' => [
                                '&id' => [
                                    'type' => 'integer',
                                ],
                                'code' => [
                                    'type' => 'varchar',
                                    'length' => '55',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $reader->processConfig($chain);
        $this->assertEquals($expected, $reader->getDirectives());
    }

    /**
     * @test
     */
    public function testValidateDirectives()
    {
        // @TODO: add logic
    }

    /**
     * @test
     */
    public function testCompileDirectives()
    {
        // @TODO: add logic
    }
}
