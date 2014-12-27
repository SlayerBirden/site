<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\App;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Observer\State;
use Maketok\Http\Request;
use Maketok\Util\ConfigConsumerInterface;
use Maketok\Util\ConfigGetter;
use Maketok\Util\RequestInterface;
use Monolog\Logger;
use Zend\Stdlib\ErrorHandler;

/**
 * Application entry point
 */
final class Site implements ConfigConsumerInterface
{
    use UtilityHelperTrait;

    const DEFAULT_TIMEZONE = 'UTC';

    const CONTEXT_SKIP_ENVIRONMENT = 0b1;
    const CONTEXT_SKIP_DISPATCH = 0b10;

    /** @var bool */
    private $terminated = false;

    /** @var bool */
    private $envInitialized = false;

    /**
     * @var array
     */
    private static $config;

    /**
     * launch app process
     * @codeCoverageIgnore
     * @param string $env
     * @param int    $context
     */
    public function run($env = '', $context = null)
    {
        if ($this->terminated) {
            return;
        }
        define('APPLICATION_ROOT', dirname(dirname(dirname(__DIR__))));
        define('AR', APPLICATION_ROOT);
        define('DS', DIRECTORY_SEPARATOR);
        define('ENV', $env);
        $this->initConfig();
        if (!($context & self::CONTEXT_SKIP_ENVIRONMENT)) {
            $this->initEnvironment();
        }
        $this->initRequest();
        $this->ioc()->set('site', $this);
        $this->getDispatcher()->notify('ioc_container_initialized', new State([]));
        if ($this->ioc()->isFrozen()) {
            // container can be already compiled
            $this->getDispatcher()->notify('ioc_container_compiled', new State([]));
        }
        // we've done our job to init system
        // now we may or may not apply configs/or run dispatcher
        if (!($context & self::CONTEXT_SKIP_DISPATCH)) {
            $this->getDispatcher()->notify('dispatch', new State(['request' => $this->ioc()->get('request')]));
        }
        $this->terminate();
    }

    /**
     * @codeCoverageIgnore
     * @internal param StateInterface
     * @throws mixed
     */
    public function terminate()
    {
        if (!$this->terminated) {
            if ($this->envInitialized) {
                ErrorHandler::stop(true);
                restore_exception_handler();
            }
            $this->terminated = true;
        }
    }

    /**
     * @codeCoverageIgnore
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $request->setSession($this->getSession());
        $this->ioc()->set('request', $request);
    }

    /**
     * init evn
     * @codeCoverageIgnore
     */
    private function initEnvironment()
    {
        date_default_timezone_set(self::DEFAULT_TIMEZONE);
        ErrorHandler::start(\E_ALL);
        set_exception_handler([$this, 'maketokExceptionHandler']);
        $this->envInitialized = true;
    }

    /**
     * init request
     * @codeCoverageIgnore
     */
    private function initRequest()
    {
        /** @var Request $request */
        $request = Request::createFromGlobals();
        $request->setArea(ENV);
        $this->setRequest($request);
    }

    /**
     * Custom exception handler
     * @codeCoverageIgnore
     * @param \Exception $e
     */
    public function maketokExceptionHandler(\Exception $e)
    {
        try {
            /** @var Logger $logger */
            $logger = $this->ioc()->get('logger');
            if ($e instanceof \ErrorException) {
                $errno = $e->getSeverity();
                if ($errno & E_NOTICE || $errno & E_USER_NOTICE) {
                    $logger->notice($e->__toString());
                } elseif ($errno & E_WARNING || $errno & E_USER_WARNING) {
                    $logger->warn($e->__toString());
                } elseif ($errno & E_ERROR || $errno & E_RECOVERABLE_ERROR || $errno & E_USER_ERROR) {
                    $logger->err($e->__toString());
                    $this->getDispatcher()->notify('application_error_triggered', new State([
                        'exception' => $e,
                        'message' => $e->__toString(),
                    ]));
                }
            } else {
                $message = sprintf("Unhandled exception\n%s", $e->__toString());
                $logger->emergency($message);
                $this->getDispatcher()->notify('application_error_triggered', new State([
                    'exception' => $e,
                    'message' => $message,
                ]));
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
     * get config value
     * @param string $path
     * @return mixed
     */
    public static function getConfig($path = null)
    {
        $path = trim($path, "/ ");
        if ($path) {
            $config = self::$config;
            while (($pos = strpos($path, '/')) !== false &&
                isset($config[substr($path, 0, $pos)]) &&
                is_array($config[substr($path, 0, $pos)])) {
                $config = $config[substr($path, 0, $pos)];
                $path = substr($path, $pos + 1);
            }
            if (is_array($config) && array_key_exists($path, $config)) {
                return $config[$path];
            } else {
                return null;
            }
        }

        return self::$config;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function initConfig()
    {
        $configGetter = new ConfigGetter();
        $configs = $configGetter->getConfig(AR . '/config', 'config', 'local');
        self::$config = [];
        foreach ($configs as $cfg) {
            $this->parseConfig($cfg);
        }
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function parseConfig(array $config)
    {
        self::$config = array_replace(self::$config, $config);
    }
}
