<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

return [
    'modules' => [
        'columns' => [
            'module_code' => [
                'type' => 'varchar',
                'length' => 32
            ],
            'version' => [
                'type' => 'varchar',
                'length' => 15
            ],
            'active' => [
                'type' => 'boolean',
            ],
            'created_at' => [
                'type' => 'datetime',
            ],
            'updated_at' => [
                'type' => 'datetime',
            ],
            'area' => [
                'type' => 'varchar',
                'length' => 55
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'definition' => ['module_code', 'area']
            ],
        ],
        'indices' => [
            'IDX_AREA' => [
                'type' => 'index',
                'definition' => ['area']
            ]
        ]
    ]
];
