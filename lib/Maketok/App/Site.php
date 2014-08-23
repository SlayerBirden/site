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
use Maketok\Template\TemplateCompilerPass;
use Maketok\Util\FormExtensionCompilerPass;
use Maketok\Util\FormTypeCompilerPass;
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
        } catch (\Exception $e) {
            printf("Exception thrown within the exception handler: %s", $e->__toString());
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
            if (file_exists($file) && !Config::getConfig('debug')) {
                require_once $file;
                $class = self::getSCClassName();
                self::$_sc = new $class();
            } else {
                $container = new ContainerBuilder();
                $container->addCompilerPass(new TemplateCompilerPass);
                $container->addCompilerPass(new FormExtensionCompilerPass);
                $container->addCompilerPass(new FormTypeCompilerPass);
                $container->setParameter('AR', AR);
                $container->setParameter('DS', DS);
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
        // add necessary params if no found
        if (!$container->hasParameter('validator_builder.yml.config.paths')) {
            $container->setParameter('validator_builder.yml.config.paths', []);
        }

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
