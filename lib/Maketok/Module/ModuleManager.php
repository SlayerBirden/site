<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Module;

use Maketok\App\Site;
use Maketok\Module\Model\Module;
use Maketok\Module\Model\ModuleTable;
use Maketok\Observer\State;
use Maketok\Observer\StateInterface;
use Maketok\Util\AbstractTableMapper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ModuleManager implements ExtensionInterface
{

    /** @var ModuleTable */
    protected $_tableType;
    /** @var array */
    private $_modules = [];
    /** @var array */
    private $_activeModules;

    public function __construct(AbstractTableMapper $tableType)
    {
        $this->_tableType = $tableType;
    }

    /**
     * @param string $code
     */
    public function disableModule($code)
    {
        $this->_tableType->disable($this->_initModule($code));
    }

    /**
     * @param string $code
     * @return Module
     */
    protected function _initModule($code)
    {
        return $this->_tableType->find($code);
    }

    /**
     * @param string $code
     * @return bool
     */
    public function uninstallModule($code)
    {
        // @TODO
    }

    /**
     * @param string $code
     * @return bool
     */
    public function installModule($code)
    {
        // @TODO
    }

    /**
     * @param string $code
     * @param string $version
     * @return bool
     */
    public function updateToVersion($code, $version)
    {
        // @TODO
    }

    /**
     * @param string $code
     */
    public function activateModule($code)
    {
        // @TODO
    }

    /**
     * @param StateInterface $state
     */
    public function processModuleConfig(StateInterface $state)
    {
        // @TODO
    }

    /**
     * @return array
     */
    public function getActiveModules()
    {
        if (is_null($this->_activeModules)) {
            $activeDbModulesResultSet = $this->_tableType->getActiveModules();
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
     * @param StateInterface
     */
    public function processModules()
    {
        if (empty($this->_modules)) {
            return;
        }
        // update db
        foreach ($this->_modules as $code => $moduleConfig) {
            if ($module = $this->_tableType->find($code)) {
                if ($moduleConfig->getInstallProcessType() == $moduleConfig::INSTALL_PROCESS_TYPE_ONLOAD) {
                    $module->version = $moduleConfig->getVersion();
                }
            } else {
                /** @var ConfigInterface $moduleConfig */
                $module = new Module;
                if ($moduleConfig->getInstallProcessType() == $moduleConfig::INSTALL_PROCESS_TYPE_ONLOAD) {
                    $module->version = $moduleConfig->getVersion();
                } else {
                    $module->version = '0';
                }
                $module->state = Module::MODULE_STATE_ACTIVE;
                $module->module_code = $moduleConfig->getCode();
            }
            $this->_tableType->save($module);
        }
        // process active modules
        Site::getServiceContainer()->get('subject_manager')->notify('modulemanager_process_before',
            new State(array('active_modules' => $this->getActiveModules())));
        foreach ($this->getActiveModules() as $config) {
            // before
            /** @var ConfigInterface $config */
            $config->initBefore();
        }
        foreach ($this->getActiveModules() as $config) {
            // events
            $config->initListeners();
        }
        Site::getServiceContainer()->get('subject_manager')->notify('modulemanager_init_listeners_after',
            new State(array('active_modules' => $this->getActiveModules())));
        foreach ($this->getActiveModules() as $config) {
            // routes
            $config->initRoutes();
        }
        Site::getServiceContainer()->get('subject_manager')->notify('modulemanager_process_after',
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
            new FileLocator(__DIR__.'/Resource/config')
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
}
