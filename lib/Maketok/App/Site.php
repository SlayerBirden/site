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
use Maketok\Observer\StateInterface;
use Maketok\Observer\SubjectManager;
use Maketok\Http\Request;
use Maketok\Util\RequestInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Zend\Db\Adapter\Adapter;

final class Site
{
    const DEFAULT_TIMEZONE = 'UTC';
    const ERROR_LOG = 'error.log';
    const SERVICE_CONTAINER_CLASS = 'MaketokServiceContainer';
    const SERVICE_CONTAINER_ADMIN_CLASS = 'MaketokAdminServiceContainer';

    /** @var  ContainerBuilder */
    private static $_sc;

    /** @var  bool */
    private static $safeRun;
    /** @var  bool */
    private static $admin;

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
     * admin - if set to true, we're working with admin interface
     *
     * @param bool $safeRun
     * @param bool $env
     * @param bool $admin
     */
    public static function run($safeRun = false, $env = true, $admin = false)
    {
        define('APPLICATION_ROOT', dirname(dirname(dirname(__DIR__))));
        define('AR', APPLICATION_ROOT);
        define('DS', DIRECTORY_SEPARATOR);
        // register modules loader
        $loader = new Autoload();
        $loader->register();

        self::$safeRun = $safeRun;
        self::$admin = $admin;

        self::_loadConfigs();
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

    /**
     * init evn
     */
    private static function _initEnvironment()
    {
        date_default_timezone_set(self::DEFAULT_TIMEZONE);
        self::setRequest(Request::createFromGlobals());
        // TODO init error handler, exception handler
    }

    /**
     * @return ContainerBuilder
     */
    public static function getServiceContainer()
    {
        if (is_null(self::$_sc)) {
            // get cached file
            $file = self::getContainerFileName();
            if (file_exists($file) && !Config::getConfig('debug')) {
                require_once $file;
                $class = self::getSCClassName();
                self::$_sc = new $class();
            } else {
                $container = new ContainerBuilder();
                $container->setParameter('application_root', AR);
                $container->setParameter('debug', Config::getConfig('debug'));
                $container->setParameter('log_dir', AR . DS . 'var' . DS . 'logs' . DS);
                $container->setParameter('cache_dir', AR . DS . 'var' . DS . 'cache' . DS);
                $loader = new YamlFileLoader($container, new FileLocator(AR . DS . 'config'));
                $loader->load('services.yml');
                if (file_exists(AR . DS . 'config' . DS . 'local.services.yml')) {
                    $loader->load('local.services.yml');
                }
                if (self::$safeRun && file_exists(AR . DS . 'config' . DS . 'dev.services.yml')) {
                    $loader->load('dev.services.yml');
                }
                if (self::$admin && file_exists(AR . DS . 'config' . DS . 'admin.services.yml')) {
                    $loader->load('admin.services.yml');
                }
                self::$_sc = $container;
            }
        }
        return self::$_sc;
    }

    /**
     * @param bool $withNS
     * @return string
     */
    private static function getSCClassName($withNS = true)
    {
        if (self::$admin) {
            $scCN = self::SERVICE_CONTAINER_ADMIN_CLASS;
        } else {
            $scCN = self::SERVICE_CONTAINER_CLASS;
        }
        if ($withNS) {
            return '\\' . $scCN;
        } else {
            return $scCN;
        }
    }

    /**
     * @param StateInterface $state
     */
    public static function serviceContainerProcessModules(StateInterface $state)
    {
        // we may not need to
        $container = self::getServiceContainer();
        $class = self::getSCClassName();
        if ($container instanceof $class) {
            return;
        }
        $activeModules = $state->modules;
        foreach ($activeModules as $moduleConfig) {
            // include each module into sc
            // only the ones that work :)
            if ($moduleConfig instanceof ExtensionInterface) {
                $container->registerExtension($moduleConfig);
                $container->loadFromExtension($moduleConfig->getAlias());
            }
        }
    }

    /**
     * @return string
     */
    protected static function getContainerFileName()
    {
        if (self::$admin) {
            $fn = 'container_admin.php';
        } else {
            $fn = 'container.php';
        }
        return AR . DS . 'var' . DS . 'cache' . DS . $fn;
    }

    /**
     * @param StateInterface $state
     */
    public static function scCompileAndDump(StateInterface $state)
    {
        $container = self::getServiceContainer();
        $class = self::getSCClassName();
        if ($container instanceof $class) {
            return;
        }
        $container->compile();

        if (!Config::getConfig('debug')) {
            $dumper = new PhpDumper($container);
            file_put_contents(
                self::getContainerFileName(),
                $dumper->dump(array('class' => self::getSCClassName(false)))
            );
        }
    }

    /**
     * @return Adapter
     */
    public static function getAdapter()
    {
        return self::getServiceContainer()->get('adapter');
    }

    public static function registry()
    {
        return self::getServiceContainer()->get('registry');
    }

    /**
     * @return SubjectManager
     */
    public static function getSubjectManager()
    {
        return self::getServiceContainer()->get('subject_manager');
    }

    /**
     * @return string
     */
    public static function getBaseUrl()
    {
        return self::getServiceContainer()->getParameter('base_url');
    }

}