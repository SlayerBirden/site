<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
Maketok\App\Config::loadConfig(dirname(__DIR__) . '/src/admin/config.php');
Maketok\App\Site::run(\Maketok\App\Site::MODE_ADMIN);
