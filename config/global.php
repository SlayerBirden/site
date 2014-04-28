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
                'subscriber' => 'Maketok\Mvc\Controller\Front::dispatch',
                'priority' => 1,
            ],
            [
                'subscriber' => 'Maketok\App\Session::init',
                'priority' => 0,
            ],
        ],
        'installer_before_process' => [
            [
                'subscriber' => 'Maketok\App\ModuleManager::processModuleConfig',
                'priority' => 1,
            ],
        ],
        'installer_after_process' => [
            [
                'subscriber' => 'Maketok\App\ModuleManager::processModules',
                'priority' => 1,
            ],
        ],
    ],
    'db_ddl' => [
        '\Maketok\App\Session\DbHandler',
        '\Maketok\App\ModuleManager',
    ],
    'session_storage' => 'db',
];