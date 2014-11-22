<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR .
    'lib' .  DIRECTORY_SEPARATOR .
    'Maketok' .  DIRECTORY_SEPARATOR .
    'App' .  DIRECTORY_SEPARATOR .
    'Config.php';
\Maketok\App\Config::loadConfig('config.php');

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';
Maketok\App\Site::run(\Maketok\App\Site::MODE_ADMIN);
