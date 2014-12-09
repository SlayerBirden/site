<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App;

use Maketok\Loader\Autoload;
use Maketok\Observer\State;
use Maketok\Observer\StateInterface;
use Maketok\Http\Request;
use Maketok\Util\RequestInterface;
use Maketok\Util\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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
    /** load base + test, not load session,ddl configs, not load env */
    const MODE_TEST = 0b010011100110011;
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
    const MODE_DUMP_SC = 0b1000000;
    const MODE_LOAD_ENVIRONMENT = 0b10000000;
    const MODE_LOAD_SC = 0b100000000;
    const MODE_LOAD_BASE_CONFIGS = 0b1000000000;
    const MODE_LOAD_TEST_CONFIGS = 0b10000000000;
    const MODE_LOAD_DEV_CONFIGS = 0b100000000000;
    const MODE_LOAD_ADMIN_CONFIGS = 0b1000000000000;
    const MODE_APPLY_CONFIGS = 0b10000000000000;
    const MODE_DISPATCH = 0b100000000000000;

    /** @var  ContainerBuilder */
    private static $sc;
    /** @var int */
    private static $mode;

    /** @var bool */
    private static $terminated;

    /** @var array  */
    private static $fileList = ['services', 'parameters'];

    /**
     * @var array
     */
    private static $envList = [
        'base',
        'dev',
        'test',
        'admin'
    ];

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

        self::loadConfigs();
        if (self::$mode & self::MODE_LOAD_ENVIRONMENT) {
            self::initEnvironment();
        }
        // we've done our job to init system
        // now we may or may not apply configs/or run dispatcher
        if (self::$mode & self::MODE_APPLY_CONFIGS) {
            self::applyConfigs();
        }
        if (!(self::$mode & self::MODE_DISPATCH)) {
            return;
        }
        self::getSC()->get('subject_manager')->notify('dispatch', new State(array(
            'request' => self::getSC()->get('request'),
        )));
        self::terminate();
    }

    /**
     * @internal param StateInterface
     * @throws mixed
     */
    public static function terminate()
    {
        if (!self::$terminated) {
            ErrorHandler::stop(true);
            restore_exception_handler();
            self::$terminated = true;
        }
    }

    /**
     * @return null|\Maketok\Http\SessionInterface
     */
    public static function getSession()
    {
        if (self::getSC()->get('request')) {
            return self::getSC()->get('request')->getSession();
        }
        return null;
    }

    /**
     * @param RequestInterface $request
     */
    public static function setRequest(RequestInterface $request)
    {
        if (self::$mode & Config::SESSION) {
            $request->setSession(self::getSC()->get('session_manager'));
        }
        self::getSC()->set('request', $request);
    }

    /**
     * load configs
     */
    private static function loadConfigs()
    {
        Config::loadConfig();
    }

    /**
     * apply configs
     */
    private static function applyConfigs()
    {
        Config::applyConfig(self::$mode);
    }

    /**
     * init evn
     */
    private static function initEnvironment()
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
            $logger = self::getSC()->get('logger');
            if ($e instanceof \ErrorException) {
                $errno = $e->getSeverity();
                if ($errno & E_NOTICE || $errno & E_USER_NOTICE) {
                    $logger->notice($e->__toString());
                } elseif ($errno & E_WARNING || $errno & E_USER_WARNING) {
                    $logger->warn($e->__toString());
                } elseif ($errno & E_ERROR || $errno & E_RECOVERABLE_ERROR || $errno & E_USER_ERROR) {
                    $logger->err($e->__toString());
                    self::getSC()->get('subject_manager')->notify('application_error_triggered', new State(array(
                        'exception' => $e,
                        'message' => $e->__toString(),
                    )));
                }
            } else {
                $message = sprintf("Unhandled exception\n%s", $e->__toString());
                $logger->emergency($message);
                self::getSC()->get('subject_manager')->notify('application_error_triggered', new State(array(
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
        if (is_null(self::$sc)) {
            // get cached file
            $file = self::getContainerFileName();
            if (file_exists($file) && !Config::getConfig('di_parameters/debug')) {
                require_once $file;
                $class = self::getSCClassName();
                self::$sc = new $class();
            } else {
                self::createSC();
            }
        }
        return self::$sc;
    }

    /**
     * alias
     * @return ContainerBuilder
     */
    public static function getSC()
    {
        return self::getServiceContainer();
    }

    /**
     * Init Service Container
     */
    private static function createSC()
    {
        self::$sc = new ContainerBuilder();
        foreach (Config::getConfig('di_compiler_passes') as $compilerPassName) {
            self::$sc->addCompilerPass(new $compilerPassName());
        }
        foreach (Config::getConfig('di_parameters') as $k => $v) {
            self::$sc->setParameter($k, $v);
        }
        self::loadSCConfig();
    }

    /**
     * load SC configs
     */
    private static function loadSCConfig()
    {
        $loader = new YamlFileLoader(self::$sc, new FileLocator(AR . '/config/di'));
        // load base configs
        foreach (self::$fileList as $fileName) {
            foreach (self::$envList as $envCode) {
                $constantCode = strtoupper("mode_load_{$envCode}_configs");
                if (defined("self::$constantCode")) {
                    $const = constant("self::$constantCode");
                } else {
                    continue;
                }
                if (self::$mode & $const) {
                    try {
                        $loader->load(self::getSCFilePrefix($envCode) . $fileName . '.yml');
                        $loader->load(self::getSCFilePrefix(['local', $envCode]) . $fileName . '.yml');
                    } catch (\InvalidArgumentException $e) {
                        // non existing files
                        // mute exception
                    }
                }
            }
        }
        // now handle some registered lib extensions
        foreach (Config::getConfig('di_extensions') as $className) {
            /** @var DependencyConfigExtensionInterface $ext */
            $ext = new $className();
            $ext->loadConfig($loader);
        }
    }

    /**
     * @param string|string[] $code
     * @return string
     */
    public static function getSCFilePrefix($code)
    {
        $fn = '';
        if (is_array($code)) {
            foreach ($code as $singleCode) {
                $fn .= self::getSCFilePrefix($singleCode);
            }
        } elseif ($code != 'base') {
            $fn .= $code . '.';
        }
        return $fn;
    }

    /**
     * @param ExtensionInterface $extension
     */
    protected static function addDiExtension(ExtensionInterface $extension)
    {
        self::$sc->registerExtension($extension);
        self::$sc->loadFromExtension($extension->getAlias());
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
        $container = self::getSC();
        $class = self::getSCClassName();
        if ($container instanceof $class) {
            return;
        }
        $activeModules = $state->modules;
        foreach ($activeModules as $moduleConfig) {
            // include each module into sc
            // only the ones that work :)
            if ($moduleConfig instanceof ExtensionInterface) {
                self::addDiExtension($moduleConfig);
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
        return AR . '/var/cache/' . $fn;
    }

    /**
     * @observe config_after_process
     */
    public static function scCompileAndDump()
    {
        $file = self::getContainerFileName();
        if (!file_exists($file) || Config::getConfig('di_parameters/debug')) {
            $container = self::getSC();
            $container->compile();

            if (self::$mode & self::MODE_DUMP_SC) {
                $dumper = new PhpDumper($container);
                /** @var StreamHandler $writer */
                $writer = $container->get('lock_stream_handler');
                $writer->writeWithLock(
                    $dumper->dump(array('class' => self::getSCClassName(false))),
                    self::getContainerFileName()
                );
            }
        }
    }

    /**
     * @return string
     * @deprecated
     */
    public static function getBaseUrl()
    {
        return self::getSC()->getParameter('base_url');
    }

    /**
     * @param string $path
     * @param array $config
     * @param string $baseUrl
     * @return string
     */
    public static function getUrl($path, array $config = null, $baseUrl = null)
    {
        if (is_null($baseUrl)) {
            $baseUrl = self::getSC()->getParameter('base_url');
        }
        $uri = UriFactory::factory($baseUrl);
        // add left path delimiter even if there was one
        $path = '/' . ltrim($path, '/');
        // remove right path delimiter
        $path = rtrim($path, '/');
        if (!isset($config['wts']) || !$config['wts']) { // config Without Trailing Slash
            $path  = $path . '/';
        }
        $uri->setPath($path);
        return $uri->toString();
    }

}
