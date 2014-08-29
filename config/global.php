<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

return [
    'php_config' => [
        'display_errors' => 0,
        'error_reporting' => E_ALL & ~E_DEPRECATED,
        'date_timezone' => 'GMT',
        'max_memory_limit' => '512M',
        'max_execution_time' => 60,
    ],
    'subject_config' => [
        'dispatch' => [
            [
                'subscriber' => 'front_controller::dispatch', // class name (or service alias) and method name
                'type' => 'service', // enum: service, static (in case static method), class
                'priority' => 10, // greater means listener will be processed earlier
            ]
        ],
        'installer_before_process' => [
            [
                'subscriber' => 'module_manager::processModuleConfig',
                'type' => 'service',
                'priority' => 10,
            ],
        ],
        'installer_after_process' => [
            [
                'subscriber' => 'module_manager::processModules',
                'type' => 'service',
                'priority' => 10,
            ],
            [
                'subscriber' => 'Maketok\App\Site::scCompileAndDump',
                'type' => 'static',
                'priority' => 0,
            ],
        ],
    ],
    'db_ddl' => [
        [
            'definition' => 'module_manager', // service alias or class name
            'type' => 'service', // enum: service, class
            'process' => 'onload', // enum: onload, ondemand
            'priority' => 0, // greater means installer will process module earlier
        ],
        [
            'client' => 'session_save_handler',
            'type' => 'service',
            'process' => 'onload',
            'priority' => 0,
        ],
    ],
    'sc_extensions' => [
        [
            'definition' => 'module_manager', // service alias or class name
            'type' => 'service', // enum: service, class
        ],
    ],
    'debug' => false,
];
