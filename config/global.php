<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
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
                'subscriber' => 'front_controller::dispatch',
                'type' => 'service',
                'priority' => 10,
            ],
            [
                'subscriber' => 'Maketok\App\Session::init',
                'type' => 'class',
                'priority' => 100,
            ],
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
        'module_list_exists' => [
            [
                'subscriber' => 'Maketok\App\Site::serviceContainerProcessModules',
                'type' => 'static',
                'priority' => 0,
            ],
        ],
    ],
    'db_ddl' => [
        '\Maketok\App\Session\DbHandler',
        '\Maketok\App\ModuleManager',
    ],
    'debug' => false,
];