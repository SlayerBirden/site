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
            ],
            'updated_at' => [
                'type' => 'datetime',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'definition' => ['module_code'],
            ]
        ],
    ]
];
