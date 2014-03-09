<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace Maketok\App;

use Maketok\Loader\Autoload;
use Zend\Db\Adapter\Adapter;

final class Site
{
    const DEFAULT_TIMEZONE = 'UTC';

    private function __construct()
    {
        // we can't create an object of Site
    }

    static public function run()
    {
        // register modules loader
        $loader = new Autoload();
        $loader->register();

        self::_loadConfigs();
        self::_initEnvironment();

        // run routers

    }

    static private function _loadConfigs()
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
        if (isset($_local_db_properties)) {
            self::_initAdapter($_local_db_properties);
        }
    }

    static private function _initEnvironment()
    {
        date_default_timezone_set(self::DEFAULT_TIMEZONE);
    }

    static private function _initAdapter($data)
    {
        self::registry()->adapter = new Adapter(array(
            'driver'   => 'pdo_mysql',
            'database' => $data['database'],
            'username' => $data['username'],
            'password' => $data['password'],
        ));
    }

    static public function getAdapter()
    {
        return self::registry()->adapter;
    }

    static public function registry()
    {
        return Registry::getInstance();
    }

}