<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace Maketok\App;

use Maketok\Loader\Autoload;
use Maketok\Observer\State;
use Maketok\Observer\SubjectManager;
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
     * @param bool $safeRun
     */
    static public function run($safeRun = false)
    {
        define('APPLICATION_ROOT', dirname(dirname(dirname(__DIR__))));
        // register modules loader
        $loader = new Autoload();
        $loader->register();

        self::_loadConfigs();
        self::_initEnvironment();
        // we've done our job to init system
        // if safeRun is up, we don't need dispatcher
        self::_applyConfigs($safeRun);
        if ($safeRun) {
            return;
        }
        self::getSubjectManager()->notify('dispatch', new State());
    }

    /**
     * load configs
     */
    static private function _loadConfigs()
    {
        Config::loadConfig();
    }

    /**
     * apply
     * @param bool $safeRun
     */
    static private function _applyConfigs($safeRun)
    {
        $mode = Config::ALL;
        if ($safeRun) {
            $mode = Config::PHP | Config::EVENTS;
        }
        Config::applyConfig($mode);
    }

    static private function _initEnvironment()
    {
        date_default_timezone_set(self::DEFAULT_TIMEZONE);
        self::_initAdapter(Config::getConfig('db_config'));
    }

    static private function _initAdapter($data)
    {
        $adapter = new Adapter(array(
            'driver'   => 'pdo_mysql',
            'database' => $data['database'],
            'username' => $data['username'],
            'password' => $data['password'],
        ));
        GlobalAdapterFeature::setStaticAdapter($adapter);
    }

    static public function getAdapter()
    {
        return GlobalAdapterFeature::getStaticAdapter();
    }

    static public function registry()
    {
        return Registry::getInstance();
    }

    /**
     * @return SubjectManager
     */
    static public function getSubjectManager()
    {
        return SubjectManager::getInstance();
    }

}