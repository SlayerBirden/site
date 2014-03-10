<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\App;

class Config
{
    static protected $_config = array();

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
            $_configDir = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR .  'config';
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
    }

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