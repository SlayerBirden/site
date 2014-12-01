<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Module;

use Maketok\App\Site;
use Maketok\Http\SessionInterface;
use Maketok\Installer\Ddl\ClientInterface;
use Maketok\Module\Model\Module;
use Maketok\Module\Model\ModuleTable;
use Maketok\Observer\State;
use Maketok\Observer\SubjectManagerInterface;
use Maketok\Util\AbstractTableMapper;
use Maketok\Util\DirectoryHandlerInterface;
use Maketok\Util\Exception\ModelException;
use Monolog\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

class ModuleManager implements ExtensionInterface, ClientInterface
{

    /** @var ModuleTable */
    protected $_tableType;
    /** @var array */
    private $_modules = [];
    /** @var array */
    private $_activeModules;
    /** @var array */
    private $_moduleDirs;
    /**
     * @var SubjectManagerInterface
     */
    private $sm;
    /**
     * @var DirectoryHandlerInterface
     */
    private $dh;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param AbstractTableMapper $tableType
     * @param DirectoryHandlerInterface $dh
     * @param SubjectManagerInterface $sm
     * @param Logger $logger
     * @param SessionInterface $session
     */
    public function __construct(AbstractTableMapper $tableType,
                                DirectoryHandlerInterface $dh,
                                SubjectManagerInterface $sm,
                                Logger $logger,
                                SessionInterface $session)
    {
        $this->_tableType = $tableType;
        $this->dh = $dh;
        $this->sm = $sm;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * @param string $code
     */
    public function disableModule($code)
    {
        try {
            $module = $this->_initModule($code);
            $module->active = 0;
            $this->_tableType->save($module);
        } catch (ModelException $e) {
            $this->logger->err($e->getMessage());
            $this->session->getFlashBag()->add('error', 'Could not update disable.');
        }
    }

    /**
     * @param string $code
     * @return Module
     * @throws ModelException
     */
    protected function _initModule($code)
    {
        return $this->_tableType->find($code);
    }

    /**
     * to uninstall module you need to move it out of "modules" directory
     *
     * @param string $code
     * @return bool
     */
    public function uninstallModule($code)
    {
        // @TODO implement move
    }

    /**
     * to install module you need to add it to "modules" directory
     *
     * @param string $code
     * @return bool
     */
    public function installModule($code)
    {
        // @TODO implement add
    }

    /**
     * @param string $code
     * @param string $version
     * @return bool
     */
    public function updateToVersion($code, $version)
    {
        try {
            $module = $this->_initModule($code);
            $module->version = $version;
            $this->_tableType->save($module);
        } catch (ModelException $e) {
            $this->logger->err($e->getMessage());
            $this->session->getFlashBag()->add('error', 'Could not update to version.');
        }
    }

    /**
     * @param string $code
     */
    public function activateModule($code)
    {
        try {
            $module = $this->_initModule($code);
            $module->active = 1;
            $this->_tableType->save($module);
        } catch (ModelException $e) {
            $this->logger->err($e->getMessage());
            $this->session->getFlashBag()->add('error', 'Could not update activate.');
        }
    }

    /**
     * @return array
     */
    public function getActiveModules()
    {
        if (is_null($this->_activeModules)) {
            $activeDbModulesResultSet = $this->_tableType->fetchFilter(array('active' => 1));
            $activeDbModuleCodes = [];
            foreach ($activeDbModulesResultSet as $module) {
                $activeDbModuleCodes[] = $module->module_code;
            }
            $this->_activeModules = array_filter($this->_modules, function($var) use ($activeDbModuleCodes) {
                return in_array($var, $activeDbModuleCodes);
            });
        }
        return $this->_activeModules;
    }

    /**
     * @param $code
     * @return null|Module
     */
    public function getActiveModule($code)
    {
        $am = $this->getActiveModules();
        return isset($am[$code]) ? $am[$code] : null;
    }

    /**
     * @param $code
     * @return null|Module
     */
    public function getModule($code)
    {
        return isset($this->_modules[$code]) ? $this->_modules[$code] : null;
    }

    /**
     * @return mixed
     */
    public function getModuleDirectories()
    {
        if (is_null($this->_moduleDirs)) {
            $this->_moduleDirs = $this->dh->ls($this->getDir());
        }
        return $this->_moduleDirs;
    }

    /**
     * @return string
     */
    protected function getDir()
    {
        return AR . DS . 'modules';
    }

    /**
     * @internal param StateInterface $state
     */
    public function processModuleConfig()
    {
        $configFileName = Site::getSC()->getParameter('module_config_file_name');
        $configName = Site::getSC()->getParameter('module_config_name');
        foreach ($this->getModuleDirectories() as $dir) {
            if (file_exists($this->getDir() . DS . $dir . DS . $configFileName)) {
                include_once $this->getDir() . DS . $dir . DS . $configFileName;
                $className = "\\modules\\$dir\\$configName";
                /** @var ConfigInterface $config */
                $config = new $className();
                array_push($this->_modules, $config);
            }
        }
        $this->sm->notify('module_list_exists', new State(array(
            'modules' => $this->_modules
        )));
    }

    /**
     * @param StateInterface
     */
    public function processModules()
    {
        if (empty($this->_modules)) {
            return;
        }
        // process active modules
        $this->sm->notify('modulemanager_process_before',
            new State(array('active_modules' => $this->getActiveModules())));
        foreach ($this->getActiveModules() as $config) {
            // events
            /** @var ConfigInterface $config */
            $config->initListeners();
        }
        $this->sm->notify('modulemanager_init_listeners_after',
            new State(array('active_modules' => $this->getActiveModules())));
        foreach ($this->getActiveModules() as $config) {
            // routes
            $config->initRoutes();
        }
        $this->sm->notify('modulemanager_process_after',
            new State(array('active_modules' => $this->getActiveModules())));
    }

    /**
     * Loads a specific configuration.
     *
     * @param array $config An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/Resource/config/di')
        );
        $loader->load('services.yml');
    }

    /**
     * Returns the namespace to be used for this extension (XML namespace).
     *
     * @return string The XML namespace
     *
     * @api
     */
    public function getNamespace()
    {
        return;
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     *
     * @api
     */
    public function getXsdValidationBasePath()
    {
        return;
    }

    /**
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * @return string The alias
     *
     * @api
     */
    public function getAlias()
    {
        return 'module_manager';
    }

    /**
     * client register dependencies (parents)
     * it must register dependencies to change resources that were created by other clients
     *
     * @return array
     */
    public function getDependencies()
    {
        // TODO: Implement getDependencies() method.
    }

    /**
     * get client version to install
     *
     * @return string
     */
    public function getDdlVersion()
    {
        return '0.2.1';
    }

    /**
     * get client config to install
     *
     * @param string $version
     * @throws Exception
     * @return array|bool
     */
    public function getDdlConfig($version)
    {
        $locator = new FileLocator(__DIR__.'/Resource/config/ddl');
        try {
            $file = $locator->locate($version.'.yml');
            $reader = new Yaml();
            return $reader->parse($file);
        } catch (\InvalidArgumentException $e) {
            // nested try
            try {
                $file = $locator->locate($version.'.php');
                return include $file;
            } catch (\InvalidArgumentException $nextE) {
                $this->logger->err($e->getMessage());
                $this->logger->err($nextE->getMessage());
            }
        }
        return false;

    }

    /**
     * get client identifier
     * must be unique
     *
     * @return string
     */
    public function getDdlCode()
    {
        return 'module_manager';
    }
}
