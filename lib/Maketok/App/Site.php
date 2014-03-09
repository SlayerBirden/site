<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace Maketok\App;

use Maketok\Loader\Autoload;

class Site
{
    const DEFAULT_TIMEZONE = 'UTC';

    public function __construct()
    {
        // do some construction stuff
    }

    public function run()
    {
        // register modules loader
        $loader = new Autoload();
        $loader->register();

        $this->_loadConfigs();
        $this->_initEnvironment();

        // run routers

    }

    private function _loadConfigs()
    {
        $basedir = dirname(dirname(dirname(__DIR__)));
        require_once $basedir . DIRECTORY_SEPARATOR .  'config' . DIRECTORY_SEPARATOR . 'global.php';
        @include_once $basedir . DIRECTORY_SEPARATOR .  'config' . DIRECTORY_SEPARATOR . 'local.php';
        // apply global var
        if (isset($_php_properties)) {
            foreach ($_php_properties as $property => $value) {
                ini_set($property, $value);
            }
        }
        // apply local var
        if (isset($_local_php_properties)) {
            foreach ($_local_php_properties as $property => $value) {
                ini_set($property, $value);
            }
        }
    }

    private function _initEnvironment()
    {
        date_default_timezone_set(self::DEFAULT_TIMEZONE);
    }
}