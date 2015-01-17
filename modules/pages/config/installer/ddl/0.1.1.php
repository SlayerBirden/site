<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
return [
    'pages' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'length' => 11,
                'nullable' => false,
                'auto_increment' => true,
                'unsigned' => true,
            ],
            'title' => [
                'type' => 'varchar',
                'length' => 200,
                'nullable' => false,
            ],
            'code' => [
                'type' => 'varchar',
                'length' => 255,
                'nullable' => false,
            ],
            'created_at' => [
                'type' => 'datetime',
                'nullable' => false,
            ],
            'updated_at' => [
                'type' => 'datetime',
                'nullable' => false,
            ],
            'content' => [
                'type' => 'text',
                'nullable' => false,
            ],
            'layout' => [
                'type' => 'text',
                'nullable' => true,
            ],
            'active' => [
                'type' => 'boolean',
                'nullable' => false,
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'definition' => 'id',
            ],
            'UNQ_KEY_CODE' => [
                'type' => 'uniqueKey',
                'definition' => 'code',
            ]
        ],
        'indices' => [
            'KEY_ACTIVE' => array(
                'type' => 'index',
                'definition' => 'active',
            ),
        ]
    ]
];
