<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace Maketok\App;

use Maketok\Loader\Autoload;
use Maketok\Observer;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

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
        self::getSubjectManager()->notify('dispatch', new Observer\State());

        // run routers

    }

    static private function _loadConfigs()
    {
        Config::loadConfig();
        Config::applyConfig();
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
     * @return Observer\SubjectManager
     */
    static public function getSubjectManager()
    {
        return Observer\SubjectManager::getInstance();
    }

}