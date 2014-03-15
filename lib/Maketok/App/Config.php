<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\App;

use Maketok\App\Ddl\Installer;
use Maketok\App\Session\DbHandler;

class Config
{
    static protected $_config = array();

    /**
     * @param array $config1
     * @param array $config2
     * @return array
     */
    static public function merge(array $config1, array $config2)
    {
        // recursive merge distinct implementation
        $_merged = $config1;
        foreach ($config2 as $key => &$value) {
            if (isset($_merged[$key]) && is_array($_merged[$key]) && is_array($value)) {
                $_merged[$key] = self::merge($_merged[$key], $value);
            } else {
                $_merged[$key] = $value;
            }
        }
        return $_merged;
    }

    /**
     * @param array $config
     */
    static public function add(array $config)
    {
        self::$_config = self::merge(self::$_config, $config);
    }

    /**
     * @param array|string $paths
     */
    static public function loadConfig($paths = null)
    {
        if (is_null($paths)) {
            $_configDir = APPLICATION_ROOT . DIRECTORY_SEPARATOR .  'config';
            $paths = array($_configDir . DIRECTORY_SEPARATOR . 'global.php',
                $_configDir . DIRECTORY_SEPARATOR . 'local.php');
        }
        if (is_string($paths)) {
            $paths = array($paths);
        }
        foreach ($paths as $path) {
            $config = include $path;
            if (is_array($config)) {
                self::add($config);
            }
        }
    }

    /**
     * basic Service Manager
     */
    static public function applyConfig()
    {
        // php
        foreach (self::getConfig('php_config') as $key => $value) {
            @ini_set($key, $value);
        }
        // events
        foreach (self::getConfig('subject_config') as $subjectName => $subjectData) {
            foreach ($subjectData as $data) {
                list($subClass, $subMethod) = explode('::', $data['subscriber']);
                $subcriber = self::classFactory($subClass);
                $priority = (isset($data['priority']) ? $data['priority'] : null);
                Site::getSubjectManager()->attach($subjectName, array($subcriber, $subMethod), $priority);
            }
        }
        // session storage
        switch (self::getConfig('session_storage')) {
            case 'db':
                Site::registry()->session_save_handler = new DbHandler();
                break;
        }
        // ddl installer
        $installer = new Installer();
        foreach (self::getConfig('db_ddl') as $client) {
            $installer->addClient($client);
        }
    }

    /**
     * Factory Method
     * @param $className
     * @return mixed
     */
    static public function classFactory($className)
    {
        return new $className;
    }

    /**
     * @param null $key
     * @return mixed
     */
    static public function getConfig($key = null)
    {
        if (is_null($key)) {
            return self::$_config;
        }
        if (isset(self::$_config[$key])) {
            return self::$_config[$key];
        }
        return array();
    }
}