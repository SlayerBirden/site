<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

return [
    'modules' => [
        'columns' => [
            'module_code' => [
                'type' => 'varchar',
                'length' => 32,
            ],
            'version' => [
                'type' => 'varchar',
                'length' => 15,
            ],
            'active' => [
                'type' => 'integer',
                'length' => 1
            ],
            'installed' => [
                'type' => 'integer',
                'length' => 1
            ],
            'updated_at' => [
                'type' => 'datetime',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'def' => 'module_code',
            ]
        ],
    ]
];
