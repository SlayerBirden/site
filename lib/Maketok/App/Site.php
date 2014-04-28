<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace Maketok\App;

use Maketok\Loader\Autoload;
use Maketok\Mvc\Router\Stack;
use Maketok\Observer\State;
use Maketok\Observer\SubjectManager;
use Maketok\Http\Request;
use Maketok\Util\RequestInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

final class Site
{
    const DEFAULT_TIMEZONE = 'UTC';

    private function __construct()
    {
        // we can't create an object of Site
    }

    /**
     * launch app process - can apply safeRun flag which minimizes prepare procedures
     *
     * run accepts 2 params:
     * safeRun decides what parts of config to apply
     * evn - should we init our env
     *
     * @param bool $safeRun
     * @param bool $env
     */
    public static function run($safeRun = false, $env = true)
    {
        define('APPLICATION_ROOT', dirname(dirname(dirname(__DIR__))));
        // register modules loader
        $loader = new Autoload();
        $loader->register();

        self::_loadConfigs();
        self::_initAdapter(Config::getConfig('db_config'));
        if ($env) {
            self::_initEnvironment();
        }
        // we've done our job to init system
        // if safeRun is up, we don't need dispatcher
        self::_applyConfigs($safeRun);
        if ($safeRun) {
            return;
        }
        self::getSubjectManager()->notify('dispatch', new State(array(
            'request' => self::getRequest()
        )));
    }

    /**
     * @return Request
     */
    public static function getRequest()
    {
        return self::registry()->request;
    }

    /**
     * @param RequestInterface $request
     */
    public static function setRequest(RequestInterface $request)
    {
        self::registry()->request = $request;
    }

    /**
     * load configs
     */
    private static function _loadConfigs()
    {
        Config::loadConfig();
    }

    /**
     * apply
     * @param bool $safeRun
     */
    private static function _applyConfigs($safeRun)
    {
        $mode = Config::ALL;
        if ($safeRun) {
            $mode = Config::PHP | Config::EVENTS;
        }
        Config::applyConfig($mode);
    }

    private static function _initEnvironment()
    {
        date_default_timezone_set(self::DEFAULT_TIMEZONE);
        self::setRequest(Request::createFromGlobals());
        // TODO init error handler, exception handler
    }

    private static function _initAdapter($data)
    {
        $adapter = new Adapter(array(
            'driver'   => 'pdo_mysql',
            'database' => $data['database'],
            'username' => $data['username'],
            'password' => $data['password'],
        ));
        self::setAdapter($adapter);
    }

    /**
     * @return Adapter
     */
    public static function getAdapter()
    {
        return GlobalAdapterFeature::getStaticAdapter();
    }

    /**
     * @param Adapter $adapter
     */
    public static function setAdapter(Adapter $adapter)
    {
        GlobalAdapterFeature::setStaticAdapter($adapter);
    }

    public static function registry()
    {
        return Registry::getInstance();
    }

    /**
     * @return SubjectManager
     */
    public static function getSubjectManager()
    {
        return SubjectManager::getInstance();
    }

    /**
     * @return Stack
     */
    public static function getCurrentRouter()
    {
        if (self::registry()->router === null) {
            self::registry()->router = new Stack();
        }
        return self::registry()->router;
    }

}