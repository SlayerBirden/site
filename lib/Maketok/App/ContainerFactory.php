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

use Maketok\Observer\StateInterface;
use Maketok\Util\StreamHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * IoC Container Factory
 * @codeCoverageIgnore
 */
class ContainerFactory
{
    /** @var array  */
    private static $serviceContainerFileList = ['services', 'parameters'];

    /** @var ContainerBuilder */
    private static $ioc;

    /** @var string */
    private static $env;

    /**
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
                // this is kind of a hack: thanks to lazy loading of container parameter bag
                // to be able to check if it's frozen later
                self::$ioc->getParameterBag();
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
            new \Maketok\Module\DI(),
            new \Maketok\Http\Session\DI(),
            new \Maketok\Template\DI(),
            new \Maketok\Mvc\DI(),
            new \Maketok\Observer\DI()
        ];
    }

    /**
     * @return CompilerPassInterface[]
     */
    public static function getCompilerPasses()
    {
        return [
            new \Maketok\Template\TemplateCompilerPass(),
            new \Maketok\Template\Symfony\Form\FormExtensionCompilerPass,
            new \Maketok\Template\Symfony\Form\FormTypeCompilerPass
        ];
    }

    /**
     * yet another alias
     * @return ContainerBuilder
     */
    public static function ioc()
    {
        return self::getServiceContainer();
    }

    /**
     * Init Service Container
     */
    private static function createSC()
    {
        if (!is_null(self::$ioc)) {
            throw new \LogicException("Invalid context. Can't create service container, it already exists.");
        }
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
        self::$ioc->setParameter('ar', AR);
        self::$ioc->setParameter('ds', DS);
        self::$ioc->setParameter('env', self::$env);
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
     */
    private static function loadSCConfig()
    {
        $loader = new YamlFileLoader(self::$ioc, new FileLocator(AR . '/config/di'));
        // load base configs
        foreach (self::$serviceContainerFileList as $fileName) {
            $toLoad = ["$fileName.yml", "local.$fileName.yml"];
            if ($env = self::getEnv()) {
                $toLoad[] = "$env.$fileName.yml";
                $toLoad[] = "local.$env.$fileName.yml";
            }
            array_walk($toLoad, function($value) use ($loader) {
                try {
                    $loader->load($value);
                } catch (\InvalidArgumentException $e) {
                    // non existing files
                    // mute exception
                }
            });
        }
        // now handle some registered lib extensions
        foreach (self::getConfigExtensions() as $ext) {
            $ext->loadConfig($loader);
        }
    }

    /**
     * @param ExtensionInterface $extension
     */
    protected static function addDiExtension(ExtensionInterface $extension)
    {
        self::ioc()->registerExtension($extension);
        self::ioc()->loadFromExtension($extension->getAlias());
    }

    /**
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
     * @param StateInterface $state
     */
    public static function serviceContainerProcessModules(StateInterface $state)
    {
        if (self::$ioc->isFrozen()) {
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
        $path = AR . '/var/cache/ioc/container';
        if ($env = self::getEnv()) {
            $path .= '.' . $env;
        }
        return $path . '.php';
    }

    /**
     * dump compile container
     */
    public static function scCompile()
    {
        if (!self::$ioc->isFrozen()) {
            self::ioc()->compile();
        }
    }

    /**
     * dump ioc container
     */
    public static function scDump()
    {
        $file = self::getContainerFileName();
        // dump only if another dump doesn't exist or if debug mode
        if (!file_exists($file) || self::getDebug()) {
            $dumper = new PhpDumper(self::$ioc);
            /** @var StreamHandler $writer */
            $writer = self::$ioc->get('lock_stream_handler');
            $writer->writeWithLock(
                $dumper->dump(array('class' => self::getContainerClassName(false))), self::getContainerFileName()
            );
        }
    }
}
