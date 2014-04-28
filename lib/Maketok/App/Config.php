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
use Maketok\Observer\State;
use Maketok\Observer\SubjectManager;

class Config
{
    static protected $_config = array();

    const ALL = 0b111111;
    const PHP = 0b1;
    const EVENTS = 0b10;
    const SESSION = 0b100;
    const DDL = 0b1000;

    /**
     * @param array $config1
     * @param array $config2
     * @return array
     */
    public static function merge(array $config1, array $config2)
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
    public static function add(array $config)
    {
        self::$_config = self::merge(self::$_config, $config);
    }

    /**
     * @param array|string $paths
     */
    public static function loadConfig($paths = null)
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
    public static function applyConfig($mode = self::ALL)
    {
        if  ($mode & self::PHP) {
            foreach (self::getConfig('php_config') as $key => $value) {
                @ini_set($key, $value);
            }
        }
        if  ($mode & self::EVENTS) {
            foreach (self::getConfig('subject_config') as $subjectName => $subjectData) {
                foreach ($subjectData as $data) {
                    list($subClass, $subMethod) = explode('::', $data['subscriber']);
                    $subcriber = self::classFactory($subClass);
                    $priority = (isset($data['priority']) ? $data['priority'] : null);
                    Site::getSubjectManager()->attach($subjectName, array($subcriber, $subMethod), $priority);
                }
            }
        }
        if  ($mode & self::SESSION) {
            switch (self::getConfig('session_storage')) {
                case 'db':
                    Site::registry()->session_save_handler = new DbHandler();
                    break;
            }
        }
        if  ($mode & self::DDL) {
            $installer = new Installer();
            foreach (self::getConfig('db_ddl') as $client) {
                $installer->addClient($client);
            }
            Site::getSubjectManager()->notify('installer_before_process', new State(array('installer' => $installer)));
            if ($installer->hasClients()) {
                $installer->processClients();
            }
            Site::getSubjectManager()->notify('installer_after_process', new State(array('installer' => $installer)));
        }
    }

    /**
     * Factory Method
     * @param $className
     * @return mixed
     */
    public static function classFactory($className)
    {
        return new $className;
    }

    /**
     * @param null $key
     * @return mixed
     */
    public static function getConfig($key = null)
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