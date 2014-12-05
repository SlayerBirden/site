<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

require_once dirname(__DIR__) . '/lib/Maketok/App/Config.php';
\Maketok\App\Config::loadConfig(dirname(__DIR__) . '/src/admin/config.php');

require_once dirname(__DIR__) . '/vendor/autoload.php';
Maketok\App\Site::run(\Maketok\App\Site::MODE_ADMIN);
