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
            'author' => [
                'type' => 'varchar',
                'length' => 55,
                'nullable' => false,
            ],
            'content' => [
                'type' => 'text',
                'nullable' => false,
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'def' => ['id'],
            ],
            'KEY_AUTHOR' => array(
                'type' => 'index',
                'def' => ['author'],
            ),
            'KEY_DATE' => array(
                'type' => 'index',
                'def' => ['created_at'],
            ),
        ],
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
                'length' => 55,
                'nullable' => true,
            ]
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'def' => ['id'],
            ]
        ],
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
                'def' => ['id'],
            ],
            'FK_KEY_ARTICLE_ID' => array(
                'type' => 'foreignKey',
                'def' => 'article_id',
                'referenceTable' => 'blog_article',
                'referenceColumn' => 'id',
            ),
            'FK_KEY_CATEGORY_ID' => array(
                'type' => 'foreignKey',
                'def' => 'category_id',
                'referenceTable' => 'blog_category',
                'referenceColumn' => 'id',
            ),
        ],
    ]
];
