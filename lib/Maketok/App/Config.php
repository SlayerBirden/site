<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App;

use Maketok\App\Exception\ConfigException;
use Maketok\Installer\ManagerInterface;
use Maketok\Observer\State;

final class Config
{
    private static $_config = [];
    private static $_isApplied = false;

    const ALL = 0b111111;
    const PHP = 0b1;
    const EVENTS = 0b10;
    const SESSION = 0b100;
    const INSTALLER = 0b1000;

    /**
     * @param array $config1
     * @param array $config2
     * @return array
     */
    public static function merge(array $config1, array $config2)
    {
        // recursive merge distinct implementation
        // credits: http://php.net/manual/en/function.array-merge-recursive.php#92195
        $_merged = $config1;
        foreach ($config2 as $key => &$value) {
            if (isset($_merged[$key]) &&
                is_array($_merged[$key]) &&
                is_array($value) && !is_numeric($key)) {
                $_merged[$key] = self::merge($_merged[$key], $value);
            } elseif (is_numeric($key)) {
                $_merged[] = $value;
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
            $_configDir = AR . DS .  'config';
            $paths = array($_configDir . DS . 'global.php',
                $_configDir . DS . 'local.php');
        }
        if (is_string($paths)) {
            $paths = array($paths);
        }
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }
            $config = include $path;
            if (is_array($config)) {
                self::add($config);
            }
        }
    }

    /**
     * basic Service Manager
     * @throws ConfigException
     */
    public static function applyConfig($mode = self::ALL)
    {
        if (self::$_isApplied) {
            return;
        }
        if  ($mode & self::EVENTS) {
            foreach (self::getConfig('subject_config') as $subjectName => $subjectData) {
                foreach ($subjectData as $data) {
                    switch ($data['type']) {
                        case 'class':
                            list($subClass, $subMethod) = explode('::', $data['subscriber']);
                            $subscriber = self::classFactory($subClass);
                            $priority = (isset($data['priority']) ? $data['priority'] : null);
                            Site::getServiceContainer()->get('subject_manager')->attach($subjectName, array($subscriber, $subMethod), $priority);
                            break;
                        case 'static':
                        case 'closure':
                            $priority = (isset($data['priority']) ? $data['priority'] : null);
                            Site::getServiceContainer()->get('subject_manager')->attach($subjectName, $data['subscriber'], $priority);
                            break;
                        case 'service':
                            list($subService, $subMethod) = explode('::', $data['subscriber']);
                            $subscriber = self::serviceFactory($subService);
                            $priority = (isset($data['priority']) ? $data['priority'] : null);
                            Site::getServiceContainer()->get('subject_manager')->attach($subjectName, array($subscriber, $subMethod), $priority);
                            break;
                        default:
                            throw new ConfigException("Unrecognized subscriber type");
                    }
                }
            }
        }
        Site::getServiceContainer()->get('subject_manager')->notify('config_after_events_process', new State([]));
        if  ($mode & self::INSTALLER) {
            Site::getServiceContainer()->get('subject_manager')->notify('installer_ddl_before_add', new State([]));
            foreach (self::getConfig('ddl_client') as $clientCode => $client) {
                /** @var ManagerInterface $manager */
                $manager = Site::getServiceContainer()->get('installer_ddl_manager');
                switch ($client['type']) {
                    case 'class':
                        $clientModel = self::classFactory($client['key']);
                        $manager->addClient($clientModel);
                        break;
                    case 'service':
                        $clientModel = self::serviceFactory($client['key']);
                        $manager->addClient($clientModel);
                        break;
                    default:
                        throw new ConfigException("Unrecognized installer client type");
                }
            }
            Site::getServiceContainer()->get('subject_manager')->notify('installer_ddl_after_add', new State([]));
        }
        self::$_isApplied = true;
        Site::getServiceContainer()->get('subject_manager')->notify('config_after_process', new State([]));
    }

    /**
     * Factory Method
     * @param string $className
     * @return object
     */
    public static function classFactory($className)
    {
        return new $className();
    }

    /**
     * Factory Method
     * @param string $serviceName
     * @return object
     */
    public static function serviceFactory($serviceName)
    {
        return Site::getServiceContainer()->get($serviceName);
    }

    /**
     * @param string $key
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
        if (is_string($key) && (strpos($key, '/') !== false)) {
            // complex key
            $keys = explode('/', $key);
            $_conf = self::$_config;
            foreach ($keys as $key) {
                if (array_key_exists($key, $_conf)) {
                    $_conf = $_conf[$key];
                } else {
                    $_conf = [];
                    break;
                }
            }
            return $_conf;
        }
        return [];
    }
}
