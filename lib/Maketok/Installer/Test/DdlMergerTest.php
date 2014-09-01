<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Test;

use Maketok\Installer\Ddl\DdlMerger;

class DdlMergerTest extends \PHPUnit_Framework_TestCase
{

    /** @var DdlMerger */
    public $merger;

    public function setUp()
    {
        $this->merger = new DdlMerger();
    }

    public function testMerge()
    {
        $m1 = [ 'm1' => [
            'modules' => [
                'columns' => [
                    'module_code' => [
                        'type' => 'varchar',
                        'length' => 32,
                    ],
                    'created_at' => [
                        'type' => 'datetime',
                    ],
                    'version' => [
                        'type' => 'varchar',
                        'length' => 15,
                    ],
                ],
                'constraints' => [
                    'primary' => [
                        'type' => 'primaryKey',
                        'def' => 'module_code',
                    ]
                ],
            ]
        ]
        ];
        $m2 = ['m2' => [
            'modules' => [
                'columns' => [
                    'module_code' => [
                        'type' => 'varchar',
                        'length' => 32,
                    ],
                    'version' => [
                        'type' => 'integer',
                        'length' => 10,
                    ],
                ],
            ],
            'articles' => [
                'columns' => [
                    'code' => [
                        'type' => 'varchar',
                        'length' => 32,
                    ],
                    'author' => [
                        'type' => 'varchar',
                        'length' => 255,
                    ],
                ],
            ]
        ]
        ];

        $expected = [
            'shared' => [
                'modules' => [
                    'columns' => [
                        'module_code' => [
                            'type' => 'varchar',
                            'length' => 32,
                        ],
                        'created_at' => [
                            'type' => 'datetime',
                        ],
                    ],
                    'constraints' => [
                        'primary' => [
                            'type' => 'primaryKey',
                            'def' => 'module_code',
                        ]
                    ],
                    'conflicts' => [
                        'm1' => [
                            'columns' => [
                                'version' => [
                                    'type' => 'varchar',
                                    'length' => 15,
                                ],
                            ],
                        ],
                        'm2' => [
                            'columns' => [
                                'version' => [
                                    'type' => 'integer',
                                    'length' => 10,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'm2' => [
                'articles' => [
                    'columns' => [
                        'code' => [
                            'type' => 'varchar',
                            'length' => 32,
                        ],
                        'author' => [
                            'type' => 'varchar',
                            'length' => 255,
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $this->merger->merge($m1, $m2));
    }

    public function testUnMerge()
    {
        $m1 = [ 'm1' => [
            'modules' => [
                'columns' => [
                    'module_code' => [
                        'type' => 'varchar',
                        'length' => 32,
                    ],
                    'created_at' => [
                        'type' => 'datetime',
                    ],
                    'version' => [
                        'type' => 'varchar',
                        'length' => 15,
                    ],
                ],
                'constraints' => [
                    'primary' => [
                        'type' => 'primaryKey',
                        'def' => 'module_code',
                    ]
                ],
            ]
        ]
        ];
        $m2 = ['m2' => [
            'modules' => [
                'columns' => [
                    'module_code' => [
                        'type' => 'varchar',
                        'length' => 32,
                    ],
                    'version' => [
                        'type' => 'integer',
                        'length' => 10,
                    ],
                ],
            ],
            'articles' => [
                'columns' => [
                    'code' => [
                        'type' => 'varchar',
                        'length' => 32,
                    ],
                    'author' => [
                        'type' => 'varchar',
                        'length' => 255,
                    ],
                ],
            ]
        ]
        ];
        $this->merger->merge($m1, $m2);

        $expected = [ 'm1' => [
            'modules' => [
                'columns' => [
                    'module_code' => [
                        'type' => 'varchar',
                        'length' => 32,
                    ],
                    'created_at' => [
                        'type' => 'datetime',
                    ],
                    'version' => [
                        'type' => 'varchar',
                        'length' => 15,
                    ],
                ],
                'constraints' => [
                    'primary' => [
                        'type' => 'primaryKey',
                        'def' => 'module_code',
                    ]
                ],
            ]
        ]
        ];

        $this->assertEquals($expected, $this->merger->unMerge('m2'));
    }

    public function testHasConflicts()
    {
        $m1 = [ 'm1' => [
            'modules' => [
                'columns' => [
                    'version' => [
                        'type' => 'varchar',
                        'length' => 15,
                    ],
                ],
            ]
        ]
        ];
        $m2 = ['m2' => [
            'modules' => [
                'columns' => [
                    'version' => [
                        'type' => 'integer',
                        'length' => 10,
                    ],
                ],
            ],
        ]
        ];
        $this->merger->merge($m1, $m2);
        $this->assertTrue($this->merger->hasConflicts());
    }

    public function testGetConflictedKeys()
    {
        $m1 = [ 'm1' => [
            'modules' => [
                'columns' => [
                    'version' => [
                        'type' => 'varchar',
                        'length' => 15,
                    ],
                ],
            ]
        ]
        ];
        $m2 = ['m2' => [
            'modules' => [
                'columns' => [
                    'version' => [
                        'type' => 'integer',
                        'length' => 10,
                    ],
                ],
            ],
        ]
        ];
        $m3 = ['m3' => [
            'modules' => [
                'columns' => [
                    'version' => [
                        'type' => 'integer',
                        'length' => 10,
                    ],
                ],
            ],
        ]
        ];
        $this->merger->merge($m1, $m2, $m3);
        $this->assertEquals(['m1', 'm2', 'm3'], $this->merger->getConflictedKeys());
    }

    public function testGetSharedKeys()
    {
        $m1 = [ 'm1' => [
            'modules' => [
                'columns' => [
                    'version' => [
                        'type' => 'varchar',
                        'length' => 15,
                    ],
                ],
            ]
        ]
        ];
        $m2 = ['m2' => [
            'modules' => [
                'columns' => [
                    'version' => [
                        'type' => 'integer',
                        'length' => 10,
                    ],
                ],
            ],
        ]
        ];
        $m3 = ['m3' => [
            'articles' => [
                'columns' => [
                    'version' => [
                        'type' => 'integer',
                        'length' => 10,
                    ],
                ],
            ],
        ]
        ];
        $this->merger->merge($m1, $m2, $m3);
        $this->assertEquals(['m1', 'm2'], $this->merger->getSharedKeys());
    }
}
