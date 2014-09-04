<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App;

use Maketok\Loader\Autoload;
use Maketok\Observer\State;
use Maketok\Observer\StateInterface;
use Maketok\Observer\SubjectManager;
use Maketok\Http\Request;
use Maketok\Util\RequestInterface;
use Monolog\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Zend\Db\Adapter\Adapter;
use Zend\Stdlib\ErrorHandler;
use Zend\Uri\UriFactory;

final class Site
{
    const DEFAULT_TIMEZONE = 'UTC';
    const SERVICE_CONTAINER_CLASS = 'MaketokServiceContainer';
    const SERVICE_CONTAINER_ADMIN_CLASS = 'MaketokAdminServiceContainer';

    /** load base configs */
    const MODE_FRONTEND = 0b110001111111111;
    /** load base+admin */
    const MODE_ADMIN = 0b111001111111111;
    /** load base + test, not load session,ddl configs */
    const MODE_TEST = 0b010011111110011;
    /** load base + dev, all configs */
    const MODE_DEVELOPMENT = 0b110101111111111;

    /**
     * load parts
     * including Config parts:
     * const PHP = 0b1;
     * const EVENTS = 0b10;
     * const SESSION = 0b100;
     * const INSTALLER = 0b1000;
     */
    const MODE_LOAD_ENVIRONMENT = 0b10000000;
    const MODE_LOAD_SC = 0b100000000;
    const MODE_LOAD_BASE_CONFIGS = 0b1000000000;
    const MODE_LOAD_TEST_CONFIGS = 0b10000000000;
    const MODE_LOAD_DEV_CONFIGS = 0b100000000000;
    const MODE_LOAD_ADMIN_CONFIGS = 0b1000000000000;
    const MODE_APPLY_CONFIGS = 0b10000000000000;
    const MODE_DISPATCH = 0b100000000000000;

    /** @var  ContainerBuilder */
    private static $_sc;
    /** @var int */
    private static $mode;

    private static $_diParameters = [];
    private static $_diConfigs = [];

    private function __construct()
    {
        // we can't create an object of Site
        return;
    }

    /**
     * launch app process
     * this accepts integer mode flag, which specifies the logic of app
     * default mode is MODE_FRONTEND
     *
     * @param int $mode
     */
    public static function run($mode = self::MODE_FRONTEND)
    {
        self::$mode = $mode;
        define('APPLICATION_ROOT', dirname(dirname(dirname(__DIR__))));
        define('AR', APPLICATION_ROOT);
        define('DS', DIRECTORY_SEPARATOR);
        // register modules loader
        $loader = new Autoload();
        $loader->register();

        self::_loadConfigs();
        if (self::$mode & self::MODE_LOAD_ENVIRONMENT) {
            self::_initEnvironment();
        }
        // we've done our job to init system
        // if safeRun is up, we don't need dispatcher
        if (self::$mode & self::MODE_APPLY_CONFIGS) {
            self::_applyConfigs();
        }
        if (!(self::$mode & self::MODE_DISPATCH)) {
            return;
        }
        self::getSubjectManager()->notify('dispatch', new State(array(
            'request' => self::getRequest()
        )));
    }

    /**
     * @return RequestInterface|null
     */
    public static function getRequest()
    {
        return self::registry()->request;
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    public static function getSession()
    {
        if (self::getRequest()) {
            return self::getRequest()->getSession();
        }
        return null;
    }

    /**
     * @param RequestInterface $request
     */
    public static function setRequest(RequestInterface $request)
    {
        if (self::$mode & Config::SESSION) {
            $request->setSession(self::getServiceContainer()->get('session_manager'));
        }
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
     * apply configs
     */
    private static function _applyConfigs()
    {
        Config::applyConfig(self::$mode);
    }

    /**
     * init evn
     */
    private static function _initEnvironment()
    {
        date_default_timezone_set(self::DEFAULT_TIMEZONE);
        self::setRequest(Request::createFromGlobals());
        $config = Config::getConfig('php_config');
        $errLevel = E_ALL;
        if (isset($config['error_reporting'])) {
            $errLevel = $config['error_reporting'];
        }
        ErrorHandler::start($errLevel);
        set_exception_handler('Maketok\App\Site::maketokExceptionHandler');
    }

    /**
     * Custom exception handler
     * @param \Exception $e
     */
    public static function maketokExceptionHandler( \Exception $e)
    {
        try {
            /** @var Logger $logger */
            $logger = self::getServiceContainer()->get('logger');
            if ($e instanceof \ErrorException) {
                $errno = $e->getSeverity();
                if ($errno & E_NOTICE || $errno & E_USER_NOTICE) {
                    $logger->notice($e->__toString());
                } elseif ($errno & E_WARNING || $errno & E_USER_WARNING) {
                    $logger->warn($e->__toString());
                } elseif ($errno & E_ERROR || $errno & E_RECOVERABLE_ERROR || $errno & E_USER_ERROR) {
                    $logger->err($e->__toString());
                    self::getSubjectManager()->notify('application_error_triggered', new State(array(
                        'exception' => $e,
                        'message' => $e->__toString(),
                    )));
                }
            } else {
                $message = sprintf("Unhandled exception\n%s", $e->__toString());
                $logger->emergency($message);
                self::getSubjectManager()->notify('application_error_triggered', new State(array(
                    'exception' => $e,
                    'message' => $message,
                )));
            }
        } catch (\Exception $ex) {
            printf("Exception '%s' thrown within the exception handler in file %s on line %d. Previous exception: %s",
                $ex->getMessage(),
                $ex->getFile(),
                $ex->getLine(),
                $e->__toString()
            );
        }
    }

    /**
     * @return ContainerBuilder
     */
    public static function getServiceContainer()
    {
        if (is_null(self::$_sc)) {
            // get cached file
            $file = self::getContainerFileName();
            if (file_exists($file) && !Config::getConfig('di_parameters/debug')) {
                require_once $file;
                $class = self::getSCClassName();
                self::$_sc = new $class();
            } else {
                self::_createSC();
            }
        }
        return self::$_sc;
    }

    /**
     * Init Service Container
     */
    private static function _createSC()
    {
        $container = new ContainerBuilder();
        foreach (Config::getConfig('di_compiler_passes') as $compilerPassName) {
            $container->addCompilerPass(new $compilerPassName());
        }
        foreach (Config::getConfig('di_parameters') as $k => $v) {
            $container->setParameter($k, $v);
        }
        $loader = new YamlFileLoader($container, new FileLocator(AR . DS . 'config'));
        if (self::$mode & self::MODE_LOAD_BASE_CONFIGS) {
            $loader->load('services.yml');
            if (file_exists(AR . DS . 'config' . DS . 'local.services.yml')) {
                $loader->load('local.services.yml');
            }
        }
        if ((self::$mode & self::MODE_LOAD_DEV_CONFIGS) &&
            file_exists(AR . DS . 'config' . DS . 'dev.services.yml')) {
            $loader->load('dev.services.yml');
        }
        if ((self::$mode & self::MODE_LOAD_TEST_CONFIGS) &&
            file_exists(AR . DS . 'config' . DS . 'test.services.yml')) {
            $loader->load('test.services.yml');
        }
        if ((self::$mode & self::MODE_LOAD_ADMIN_CONFIGS) &&
            file_exists(AR . DS . 'config' . DS . 'admin.services.yml')) {
            $loader->load('admin.services.yml');
            if (file_exists(AR . DS . 'config' . DS . 'local.admin.services.yml')) {
                $loader->load('local.admin.services.yml');
            }
        }
        self::$_sc = $container;
        // now handle some registered lib extensions
        foreach (Config::getConfig('di_extensions') as $className) {
            $ext = new $className();
            self::_addDiExtension($ext);
        }
    }

    /**
     * @param ExtensionInterface $extension
     */
    protected static function _addDiExtension(ExtensionInterface $extension)
    {
        self::$_sc->registerExtension($extension);
        self::$_sc->loadFromExtension($extension->getAlias());
    }

    /**
     * @param bool $withNS
     * @return string
     */
    private static function getSCClassName($withNS = true)
    {
        if (self::$mode & self::MODE_LOAD_ADMIN_CONFIGS) {
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
                self::_addDiExtension($moduleConfig);
            }
        }
    }

    /**
     * @return string
     */
    protected static function getContainerFileName()
    {
        if (self::$mode & self::MODE_LOAD_ADMIN_CONFIGS) {
            $fn = 'container_admin.php';
        } else {
            $fn = 'container.php';
        }
        return AR . DS . 'var' . DS . 'cache' . DS . $fn;
    }

    /**
     * @observe config_after_process
     */
    public static function scCompileAndDump()
    {
        $file = self::getContainerFileName();
        if (!file_exists($file) || Config::getConfig('di_parameters/debug')) {
            $container = self::getServiceContainer();
            $container->compile();

            if (!Config::getConfig('di_parameters/debug')) {
                $dumper = new PhpDumper($container);
                file_put_contents(
                    self::getContainerFileName(),
                    $dumper->dump(array('class' => self::getSCClassName(false)))
                );
            }
        }
    }

    /**
     * @return Adapter
     */
    public static function getAdapter()
    {
        return self::getServiceContainer()->get('adapter');
    }

    /**
     * @return Registry
     */
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

    /**
     * @param string $path
     * @param array $config
     * @return string
     */
    public static function getUrl($path, array $config = null)
    {
        $uri = UriFactory::factory(Site::getBaseUrl());
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/');
        if (!isset($config['wts'])) { // config Without Trailing Slash
            $path  = $path . '/';
        }
        $uri->setPath($path);
        return $uri->toString();
    }

}
