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

use Maketok\Module\ConfigInterface as ModuleConfigInterface;
use Maketok\Util\StreamHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * IoC Container Factory
 * @codeCoverageIgnore
 */
class ContainerFactory implements ConfigInterface
{
    /** @var array  */
    private $serviceContainerFileList = ['parameters', 'services'];

    /** @var ContainerBuilder */
    private $ioc;

    /**
     * @var ContainerFactory
     */
    private static $instance;

    /**
     * singleton
     */
    private function __construct()
    {
    }

    /**
     * singleton
     */
    private function __clone()
    {
    }

    /**
     * @return ContainerFactory
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return ContainerBuilder
     */
    public function getServiceContainer()
    {
        if (is_null($this->ioc)) {
            // get cached file
            $file = $this->getContainerFileName();
            if (file_exists($file) && !$this->isDebug()) {
                require_once $file;
                $class = $this->getContainerClassName();
                $this->ioc = new $class();
                // this is kind of a hack: thanks to lazy loading of container parameter bag
                // to be able to check if it's frozen later
                $this->ioc->getParameterBag();
            } else {
                $this->createSC();
            }
        }
        return $this->ioc;
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return Site::getConfig('debug');
    }

    /**
     * @return ConfigInterface[]
     */
    public function getConfigExtensionPaths()
    {
        return Site::getConfig('ioc_extension_path');
    }

    /**
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses()
    {
        return Site::getConfig('iod_compiler_pass');
    }

    /**
     * yet another alias
     * @return ContainerBuilder
     */
    public function ioc()
    {
        return $this->getServiceContainer();
    }

    /**
     * Init Service Container
     */
    private function createSC()
    {
        if (!is_null($this->ioc)) {
            throw new \LogicException("Invalid context. Can't create service container, it already exists.");
        }
        $this->ioc = new ContainerBuilder();
        foreach ($this->getCompilerPasses() as $compilerPass) {
            $this->ioc->addCompilerPass($compilerPass);
        }
        $this->addDefaultParameters();
        $this->loadSCConfig();
    }

    /**
     * set default params
     */
    public function addDefaultParameters()
    {
        $this->ioc->setParameter('ar', AR);
        $this->ioc->setParameter('ds', DS);
        $this->ioc->setParameter('env', ENV);
        $this->ioc->setParameter('debug', Site::getConfig('debug'));
    }

    /**
     * load SC configs
     */
    private function loadSCConfig()
    {
        $paths = $this->getConfigExtensionPaths();
        array_unshift($paths, AR . '/config/di');
        foreach ($paths as $path) {
            $loader = new YamlFileLoader($this->ioc, new FileLocator($path));
            $this->loadConfig($loader);
        }
    }

    /**
     * @param ExtensionInterface $extension
     */
    protected function addDiExtension(ExtensionInterface $extension)
    {
        $this->ioc()->registerExtension($extension);
        $this->ioc()->loadFromExtension($extension->getAlias());
    }

    /**
     * @param bool $withNS
     * @return string
     */
    private function getContainerClassName($withNS = true)
    {
        $name = 'MaketokServiceContainer';
        // assignment on purpose, ENV may contain empty string
        if ($env = ENV) {
            $name .= ucfirst($env);
        }
        if ($withNS) {
            return '\\' . $name;
        } else {
            return $name;
        }
    }

    /**
     * @param ModuleConfigInterface[] $activeModules
     */
    public function serviceContainerProcessModules($activeModules)
    {
        if ($this->ioc->isFrozen()) {
            return;
        }
        foreach ($activeModules as $moduleConfig) {
            // include each module into sc
            // only the ones that work :)
            if ($moduleConfig instanceof ExtensionInterface) {
                $this->addDiExtension($moduleConfig);
            }
        }
    }

    /**
     * @return string
     */
    protected function getContainerFileName()
    {
        $path = AR . '/var/cache/ioc/container';
        // assignment on purpose, ENV may contain empty string
        if ($env = ENV) {
            $path .= '.' . $env;
        }
        return $path . '.php';
    }

    /**
     * dump compile container
     */
    public function scCompile()
    {
        if (!$this->ioc->isFrozen()) {
            $this->ioc()->compile();
        }
    }

    /**
     * dump ioc container
     */
    public function scDump()
    {
        $file = $this->getContainerFileName();
        // dump only if another dump doesn't exist or if debug mode
        if (!file_exists($file) || $this->isDebug()) {
            $dumper = new PhpDumper($this->ioc);
            /** @var StreamHandler $writer */
            $writer = $this->ioc->get('lock_stream_handler');
            $writer->writeWithLock(
                $dumper->dump(array('class' => $this->getContainerClassName(false))),
                $this->getContainerFileName()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfig(FileLoader $loader)
    {
        foreach ($this->serviceContainerFileList as $fileName) {
            $toLoad = ["$fileName.yml", "local.$fileName.yml"];
            // assignment on purpose, ENV may contain empty string
            if ($env = ENV) {
                $toLoad[] = "$env.$fileName.yml";
                $toLoad[] = "local.$env.$fileName.yml";
            }
            array_walk($toLoad, function ($value) use ($loader) {
                try {
                    $loader->load($value);
                } catch (\InvalidArgumentException $e) {
                    // non existing files
                    // mute exception
                }
            });
        }
    }
}
