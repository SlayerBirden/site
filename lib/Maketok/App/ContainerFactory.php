<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App;

use Maketok\Observer\State;
use Maketok\Observer\StateInterface;
use Maketok\Util\StreamHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerFactory
{
    /** @var array  */
    private static $serviceContainerFileList = ['services', 'parameters'];

    /** @var ContainerBuilder */
    private static $ioc;

    /** @var string */
    private static $env;

    /**
     * @codeCoverageIgnore
     * @return ContainerBuilder
     */
    public static function getServiceContainer()
    {
        if (is_null(self::$ioc)) {
            // get cached file
            $file = self::getContainerFileName();
            if (file_exists($file) && !self::getDebug()) {
                require_once $file;
                $class = self::getContainerClassName();
                self::$ioc = new $class();
            } else {
                self::createSC();
            }
        }
        return self::$ioc;
    }

    /**
     * @return bool
     */
    public static function getDebug()
    {
        return true;
    }

    /**
     * @return DependencyConfigExtensionInterface[]
     */
    public static function getConfigExtensions()
    {
        return [
            new \Maketok\Installer\Ddl\DI(),
            new \Maketok\Module\DI()
        ];
    }

    /**
     * @return CompilerPassInterface[]
     */
    public static function getCompilerPasses()
    {
        return [
            new \Maketok\Template\TemplateCompilerPass(),
            new \Maketok\Util\Symfony\Form\FormExtensionCompilerPass,
            new \Maketok\Util\Symfony\Form\FormTypeCompilerPass
        ];
    }

    /**
     * yet another alias
     * @codeCoverageIgnore
     * @return ContainerBuilder
     */
    public static function ioc()
    {
        return self::getServiceContainer();
    }

    /**
     * Init Service Container
     * @codeCoverageIgnore
     */
    private static function createSC()
    {
        self::$ioc = new ContainerBuilder();
        foreach (self::getCompilerPasses() as $compilerPass) {
            self::$ioc->addCompilerPass($compilerPass);
        }
        self::addDefaultParameters();
        self::loadSCConfig();
    }

    /**
     * set default params
     */
    public static function addDefaultParameters()
    {
        self::$ioc->setParameter('AR', AR);
        self::$ioc->setParameter('DS', DS);
    }

    /**
     * @return string
     */
    public static function getEnv()
    {
        return self::$env;
    }

    /**
     * @param string $env
     */
    public static function setEnv($env)
    {
        if (!is_scalar($env)) {
            throw new \InvalidArgumentException(sprintf("Invalid environment variable provided of type %s", gettype($env)));
        }
        self::$env = $env;
    }

    /**
     * load SC configs
     * @codeCoverageIgnore
     */
    private static function loadSCConfig()
    {
        $loader = new YamlFileLoader(self::$ioc, new FileLocator(AR . '/config/di'));
        // load base configs
        foreach (self::$serviceContainerFileList as $fileName) {
            try {
                $loader->load("$fileName.yml");
                $loader->load("local.$fileName.yml");
                if ($env = self::getEnv()) {
                    $loader->load("$env.$fileName.yml");
                    $loader->load("local.$env.$fileName.yml");
                }
            } catch (\InvalidArgumentException $e) {
                // non existing files
                // mute exception
            }
        }
        // now handle some registered lib extensions
        foreach (self::getConfigExtensions() as $ext) {
            $ext->loadConfig($loader);
        }
    }

    /**
     * @codeCoverageIgnore
     * @param ExtensionInterface $extension
     */
    protected static function addDiExtension(ExtensionInterface $extension)
    {
        self::ioc()->registerExtension($extension);
        self::ioc()->loadFromExtension($extension->getAlias());
    }

    /**
     * @codeCoverageIgnore
     * @param bool $withNS
     * @return string
     */
    private static function getContainerClassName($withNS = true)
    {
        $name = 'MaketokServiceContainer';
        if ($env = self::getEnv()) {
            $name .= ucfirst($env);
        }
        if ($withNS) {
            return '\\' . $name;
        } else {
            return $name;
        }
    }

    /**
     * @codeCoverageIgnore
     * @param StateInterface $state
     */
    public static function serviceContainerProcessModules(StateInterface $state)
    {
        // we may not need to
        $container = self::ioc();
        $class = self::getContainerClassName();
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
     * @codeCoverageIgnore
     * @return string
     */
    protected static function getContainerFileName()
    {
        $path = AR . '/var/cache/ioc/container';
        if ($env = self::getEnv()) {
            $path .= '.' . $env;
        }
        return $path . '.php';
    }

    /**
     * @codeCoverageIgnore
     * @observe config_after_process
     */
    public static function scCompile()
    {
        $file = self::getContainerFileName();
        // compile only if file doesn't exist, or if debug mode is on
        if (!file_exists($file) || self::getDebug()) {
            self::ioc()->compile();
            self::ioc()->get('subject_manager')->notify('ioc_container_compiled', new State([]));
        }
    }

    /**
     * @codeCoverageIgnore
     * @observe config_after_process
     */
    public static function scDump()
    {
        $file = self::getContainerFileName();
        // dump only if another dump doesn't exist or if debug mode
        if (!file_exists($file) || self::getDebug()) {
            $dumper = new PhpDumper(self::ioc());
            /** @var StreamHandler $writer */
            $writer = self::ioc()->get('lock_stream_handler');
            $writer->writeWithLock(
                $dumper->dump(array('class' => self::getContainerClassName(false))), self::getContainerFileName()
            );
        }
    }
}
