<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
return [
    'blog_article' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'length' => 11,
            ],
            'title' => [
                'type' => 'varchar',
                'length' => 55,
            ],
            'created_at' => [
                'type' => 'datetime',
            ],
            'updated_at' => [
                'type' => 'datetime',
            ],
            'author' => [
                'type' => 'varchar',
                'length' => 55,
            ],
            'content' => [
                'type' => 'text',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'def' => 'id',
            ]
        ],
    ],
    'blog_category' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'length' => 11,
            ],
            'title' => [
                'type' => 'varchar',
                'length' => 55,
            ],
            'created_at' => [
                'type' => 'datetime',
            ],
            'updated_at' => [
                'type' => 'datetime',
            ],
            'image' => [
                'type' => 'varchar',
                'length' => 55,
            ]
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'def' => 'id',
            ]
        ],
    ],
    'blog_category_article' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'length' => 11,
            ],
            'article_id' => [
                'type' => 'integer',
                'length' => 11,
            ],
            'category_id' => [
                'type' => 'integer',
                'length' => 11,
            ]
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primaryKey',
                'def' => 'id',
            ]
        ],
    ]
];