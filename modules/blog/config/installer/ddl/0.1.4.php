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
    'blog_article' => [
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
            'author' => [
                'type' => 'varchar',
                'length' => 55,
                'nullable' => false,
            ],
            'content' => [
                'type' => 'text',
                'nullable' => false,
            ],
            'description' => [
                'type' => 'text',
                'nullable' => false,
            ]
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'definition' => ['id'],
            ],
            'UNQ_KEY_CODE' => [
                'type' => 'uniqueKey',
                'definition' => ['code'],
            ]
        ],
        'indices' => [
            'KEY_AUTHOR' => array(
                'type' => 'index',
                'definition' => ['author'],
            ),
            'KEY_DATE' => array(
                'type' => 'index',
                'definition' => ['created_at'],
            )
        ]
    ],
    'blog_category' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'length' => 11,
                'nullable' => false,
                'auto_increment' => true,
                'unsigned' => true,
            ],
            'code' => [
                'type' => 'varchar',
                'length' => 255,
                'nullable' => false,
            ],
            'title' => [
                'type' => 'varchar',
                'length' => 55,
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
            'image' => [
                'type' => 'varchar',
                'length' => 255,
                'nullable' => true,
            ]
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'definition' => ['id'],
            ],
            'UNQ_KEY_CODE' => [
                'type' => 'uniqueKey',
                'definition' => ['code'],
            ]
        ]
    ],
    'blog_category_article' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'length' => 11,
                'nullable' => false,
                'auto_increment' => true,
                'unsigned' => true,
            ],
            'article_id' => [
                'type' => 'integer',
                'length' => 11,
                'nullable' => false,
                'unsigned' => true,
            ],
            'category_id' => [
                'type' => 'integer',
                'length' => 11,
                'nullable' => false,
                'unsigned' => true,
            ]
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'definition' => ['id'],
            ],
            'FK_KEY_ARTICLE_ID' => array(
                'type' => 'foreignKey',
                'column' => 'article_id',
                'reference_table' => 'blog_article',
                'reference_column' => 'id',
            ),
            'FK_KEY_CATEGORY_ID' => array(
                'type' => 'foreignKey',
                'column' => 'category_id',
                'reference_table' => 'blog_category',
                'reference_column' => 'id',
            )
        ]
    ]
];
