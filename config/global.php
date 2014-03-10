<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
return array(
    'php_config' => array(
        'display_errors' => 0,
        'error_reporting' => E_ALL & ~E_DEPRECATED,
        'date_timezone' => 'GMT',
        'max_memory_limit' => '512M',
        'max_execution_time' => 60,
    ),
    'subject_config' => array(
        'dispatch' => array(
            array(
                'subscriber' => 'Maketok\Mvc\Router\Standard::dispatch',
                'priority' => 1,
            ),
        ),
    ),
);