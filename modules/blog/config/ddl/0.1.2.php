<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
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
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'def' => ['id'],
            ],
            'UNQ_KEY_CODE' => [
                'type' => 'uniqueKey',
                'def' => ['code'],
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
                'length' => 55,
                'nullable' => true,
            ]
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'def' => ['id'],
            ],
            'UNQ_KEY_CODE' => [
                'type' => 'uniqueKey',
                'def' => ['code'],
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
