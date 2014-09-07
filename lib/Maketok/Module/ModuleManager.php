<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Module;

use Maketok\Ddl\Installer;
use Maketok\Ddl\InstallerApplicableInterface;
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

class ModuleManager implements InstallerApplicableInterface, ExtensionInterface
{

    /** @var ModuleTable */
    protected $_tableType;
    /** @var array */
    private $_modules = [];
    /** @var array */
    private $_activeModules;

    public function __construct(AbstractTableMapper $tableType, array $moduleClasses)
    {
        $this->_tableType = $tableType;
        foreach ($moduleClasses as $moduleClass) {
            $module = new $moduleClass();
            if ($module instanceof ConfigInterface) {
                $this->_modules[$module->getCode()] = $module;
            }
        }
        s::getServiceContainer()->get('subject_manager')->notify('module_list_exists', new State(array(
            'modules' => $this->_modules
        )));
    }

    /**
     * @return array
     */
    public function getDdlConfig()
    {
        return include __DIR__ .'/Resource/config/ddl/' . $this->getDdlConfigVersion() . '.php';
    }

    /**
     * @return string
     */
    public function getDdlConfigVersion()
    {
        return '0.2.1';
    }

    /**
     * @return string
     */
    public function getDdlConfigName()
    {
        return 'module_manager';
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
        /** @var Module $module */
        $module = $this->_tableType->find($code);
        if (!$module->getCanBeUninstalled()) {
            return false;
        }
        /** @var ConfigInterface $moduleConfig */
        $moduleConfig = $this->getModule($code);
        if (empty($moduleConfig)) {
            return false;
        }
        if ($moduleConfig->getInstallProcessType() == $moduleConfig::INSTALL_PROCESS_TYPE_ONLOAD) {
            // this type can't be uninstalled
            return false;
        }
        if (!($moduleConfig instanceof InstallerApplicableInterface)) {
            // this module can't be uninstalled
            return false;
        }

        /** @var Installer $ddlInstaller */
        $ddlInstaller = Site::getServiceContainer()->get('ddl_installer');
        $ddlInstaller->addClient(array(
            'def' => [],
            'name' => $module->module_code,
            'version' => $module->version,
        ));
        $ddlInstaller->processClients();

        $this->_tableType->moveToVersion($module, '0');
        return true;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function installModule($code)
    {
        /** @var Module $module */
        $module = $this->_tableType->find($code);
        if (!$module->getCanBeUninstalled()) {
            return false;
        }
        /** @var ConfigInterface $moduleConfig */
        $moduleConfig = $this->getModule($code);
        if (empty($moduleConfig)) {
            return false;
        }
        if ($moduleConfig->getInstallProcessType() == $moduleConfig::INSTALL_PROCESS_TYPE_ONLOAD) {
            // this type can't be uninstalled
            return false;
        }
        if (!($moduleConfig instanceof InstallerApplicableInterface)) {
            // this module can't be uninstalled
            return false;
        }

        /** @var Installer $ddlInstaller */
        $ddlInstaller = Site::getServiceContainer()->get('ddl_installer');
        $ddlInstaller->addClient($moduleConfig);
        $ddlInstaller->processClients();

        $this->_tableType->moveToVersion($module, $moduleConfig->getVersion());
        return true;
    }

    /**
     * @param string $code
     * @param string $version
     * @return bool
     */
    public function updateToVersion($code, $version)
    {
        /** @var Module $module */
        $module = $this->_tableType->find($code);
        if (!$module->getCanBeUninstalled()) {
            return false;
        }
        /** @var ConfigInterface $moduleConfig */
        $moduleConfig = $this->getModule($code);
        if (empty($moduleConfig)) {
            return false;
        }
        if ($moduleConfig->getInstallProcessType() == $moduleConfig::INSTALL_PROCESS_TYPE_ONLOAD) {
            // this type can't be uninstalled
            return false;
        }
        if (!($moduleConfig instanceof InstallerApplicableInterface)) {
            // this module can't be uninstalled
            return false;
        }

        /** @var Installer $ddlInstaller */
        $ddlInstaller = Site::getServiceContainer()->get('ddl_installer');
        $map = $ddlInstaller->getDdlInstallerMap();
        if (isset($map[$module->module_code][$version])) {
            $def = $map[$module->module_code][$version];
        } elseif ($version == '0') {
            $def = [];
        } else {
            // we can not update module to version not registered at Map
            // that would mean that that state wasn't ever really installed
            // and so we can't 'move back' to it
            return false;
        }
        $ddlInstaller->addClient(array(
            'def' => $def,
            'name' => $module->module_code,
            'version' => $version,
        ));
        $ddlInstaller->processClients();

        $this->_tableType->moveToVersion($module, $moduleConfig->getVersion());
        return true;
    }

    /**
     * @param string $code
     */
    public function activateModule($code)
    {
        $this->_tableType->enable($this->_initModule($code));
    }

    /**
     * @param StateInterface $state
     */
    public function processModuleConfig(StateInterface $state)
    {
        /** @var Installer $ddlInstaller */
        $ddlInstaller = $state->installer;
        // insert new modules
        foreach ($this->_modules as $config) {
            /** @var ConfigInterface $config */
            // ddl
            if (($config->getInstallProcessType() == $config::INSTALL_PROCESS_TYPE_ONLOAD)
                && $config instanceof InstallerApplicableInterface) {
                $ddlInstaller->addClient($config);
            }
        }
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
