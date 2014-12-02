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
                'type' => 'service', // enum: service, static (in case static method), class, closure
                'priority' => 10, // greater means listener will be processed earlier
            ],
        ],
        'installer_before_process' => [

        ],
        'config_after_events_process' => [
            [
                'subscriber' => 'module_manager::processModuleConfig',
                'type' => 'service',
                'priority' => 10,
            ],
            [
                'subscriber' => 'Maketok\App\Site::scCompileAndDump',
                'type' => 'static',
                'priority' => 0,
            ],

        ],
        'config_after_process' => [
            [
                'subscriber' => 'module_manager::processModules',
                'type' => 'service',
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
    'di_extensions' => ['\Maketok\Installer\Ddl\DI', '\Maketok\Module\DI'],
    'di_compiler_passes' => [
        'Maketok\Template\TemplateCompilerPass',
        'Maketok\Util\Symfony\Form\FormExtensionCompilerPass',
        'Maketok\Util\Symfony\Form\FormTypeCompilerPass',
    ],
    'di_parameters' => [
        'AR' => AR,
        'DS' => DS,
        'debug' => false,
    ],
    'ddl_client' => [
        'session' => [
            'type' => 'service', // enum: class, service
            'key' => 'session_save_handler',
        ],
        'installer_ddl' => [
            'type' => 'class',
            'key' => 'Maketok\\Installer\\Ddl\\InstallerClient',
        ],
        'module_manager' => [
            'type' => 'service',
            'key' => 'module_manager',
        ],
    ],
];
