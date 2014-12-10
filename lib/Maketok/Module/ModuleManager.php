<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Module;

use Maketok\App\Helper\ContainerTrait;
use Maketok\Http\SessionInterface;
use Maketok\Installer\Ddl\ClientInterface;
use Maketok\Module\Resource\Model\Module;
use Maketok\Observer\State;
use Maketok\Observer\SubjectManagerInterface;
use Maketok\Util\TableMapper;
use Maketok\Util\DirectoryHandlerInterface;
use Maketok\Util\Exception\ModelException;
use Monolog\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use Maketok\Installer;

class ModuleManager implements ClientInterface
{
    use ContainerTrait;

    /** @var TableMapper */
    protected $_tableType;
    /** @var array */
    private $_modules = [];
    /** @var array */
    private $_activeModules;
    /** @var array */
    private $_dbModules;
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
     * @var string
     */
    private $configName;
    /**
     * @var string
     */
    private $area;

    /**
     * @param TableMapper $tableType
     * @param DirectoryHandlerInterface $dh
     * @param SubjectManagerInterface $sm
     * @param Logger $logger
     * @param SessionInterface $session
     * @param string $configName
     * @param string $area
     */
    public function __construct(TableMapper $tableType,
                                DirectoryHandlerInterface $dh,
                                SubjectManagerInterface $sm,
                                Logger $logger,
                                SessionInterface $session,
                                $configName,
                                $area)
    {
        $this->_tableType = $tableType;
        $this->dh = $dh;
        $this->sm = $sm;
        $this->logger = $logger;
        $this->session = $session;
        $this->configName = $configName;
        $this->area = $area;
    }

    /**
     * @param string $code
     */
    public function disableModule($code)
    {
        try {
            $module = $this->initModule(array($code, $this->area));
            $module->active = 0;
            $this->_tableType->save($module);
        } catch (ModelException $e) {
            $this->logger->err($e->__toString());
            $this->session->getFlashBag()->add('error', 'Could not update disable.');
        }
    }

    /**
     * @param int|string|string[] $id
     * @return Module|array|\ArrayObject|null
     * @throws ModelException
     */
    protected function initModule($id)
    {
        return $this->_tableType->find($id);
    }

    /**
     * to uninstall module you need to move it out of "modules" directory
     *
     * @param string $code
     */
    public function uninstallModule($code)
    {
        // may not be needed
    }

    /**
     * to install module you need to add it to "modules" directory
     *
     * @param string $code
     */
    public function installModule($code)
    {
        // may not be needed
    }

    /**
     * @param string $code
     * @param string $version
     */
    public function updateToVersion($code, $version)
    {
        try {
            $module = $this->initModule(array($code, $this->area));
            $module->version = $version;
            $this->_tableType->save($module);
        } catch (ModelException $e) {
            $this->logger->err($e->__toString());
            $this->session->getFlashBag()->add('error', 'Could not update to version.');
        }
    }

    /**
     * @param string $code
     */
    public function activateModule($code)
    {
        try {
            $module = $this->initModule(array($code, $this->area));
            $module->active = 1;
            $this->_tableType->save($module);
        } catch (ModelException $e) {
            $this->logger->err($e->__toString());
            $this->session->getFlashBag()->add('error', 'Could not update activate.');
        }
    }

    /**
     * @return array
     */
    public function getActiveModules()
    {
        if (is_null($this->_activeModules)) {
            $activeDbModulesResultSet = $this->_tableType->fetchFilter(array('active' => 1, 'area' => $this->getArea()));
            $activeDbModuleCodes = [];
            foreach ($activeDbModulesResultSet as $module) {
                /** @var Module $module */
                $activeDbModuleCodes[] = $module->module_code;
            }
            $this->_activeModules = array_filter($this->_modules, function($config) use ($activeDbModuleCodes) {
                /** @var ConfigInterface $config */
                return in_array($config, $activeDbModuleCodes);
            });
        }
        return $this->_activeModules;
    }

    /**
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @return array|\Zend\Db\ResultSet\ResultSet
     */
    public function getDbModules()
    {
        if (is_null($this->_dbModules)) {
            $result = $this->_tableType->fetchFilter(array('area' => $this->area));
            $this->_dbModules = [];
            foreach ($result as $module) {
                /** @var Module $module */
                $this->_dbModules[$module->module_code] = $module;
            }
        }
        return $this->_dbModules;
    }

    /**
     * @param ConfigInterface $config
     * @return bool
     */
    public function getModuleExistsInDb(ConfigInterface $config)
    {
        $db = $this->getDbModules();
        return isset($db[$config->getCode()]);
    }

    /**
     * @param string $code
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
        $configName = $this->configName;
        foreach ($this->getModuleDirectories() as $dir) {
            if (file_exists($this->getDir() . "/$dir/$configName.php")) {
                include_once $this->getDir() . "/$dir/$configName.php";
                $className = "\\modules\\$dir\\$configName";
                /** @var ConfigInterface $config */
                $config = new $className();
                $this->_modules[$config->getCode()] = $config;
            }
        }
        $this->sm->notify('module_list_exists', new State(array(
            'modules' => $this->_modules
        )));
    }

    /**
     * @param ConfigInterface $config
     */
    public function addDbModule(ConfigInterface $config)
    {
        /** @var Module $module */
        $module = $this->_tableType->getGateway()->getResultSetPrototype()->getObjectPrototype();
        $module->module_code = $config->getCode();
        $module->active = $config->isActive();
        $module->version = $config->getVersion();
        $module->area = $this->getArea();
        $this->_tableType->save($module);
    }

    /**
     * @param Module $module
     */
    public function removeDbModule(Module $module)
    {
        $this->_tableType->delete($module->module_code);
    }

    /**
     * update Modules in current storage
     * @internal param StateInterface
     */
    public function updateModules()
    {
        if (empty($this->_modules)) {
            return;
        }
        try {
            // candidates for addition
            foreach ($this->_modules as $mConfig) {
                /** @var ConfigInterface $mConfig */
                if (!$this->getModuleExistsInDb($mConfig)) {
                    $this->addDbModule($mConfig);
                }
            }
            // candidates for deletion
            foreach ($this->getDbModules() as $module) {
                /** @var Module $module */
                $mConfig = $this->getModule($module->module_code);
                if (is_null($mConfig)) {
                    $this->removeDbModule($module);
                }
            }
            $this->sm->notify('modulemanager_updates_after',
                new State(array('active_modules' => $this->getActiveModules())));
        } catch (\Exception $e) {
            $this->logger->emerg($e->__toString());
        }
    }

    /**
     * add installer subscribers
     * @internal param StateInterface
     */
    public function addInstallerSubscribers()
    {
        try {
            foreach ($this->getActiveModules() as $config) {
                if ($config instanceof Installer\Ddl\ClientInterface) {
                    $this->ioc()->get('installer_ddl_manager')->addClient($config);
                }
            }
        } catch (\Exception $e) {
            $this->logger->emerg($e->__toString());
        }
    }

    /**
     * @internal param StateInterface
     */
    public function processModules()
    {
        if (empty($this->_modules)) {
            return;
        }
        try {
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
        } catch (\Exception $e) {
            $this->logger->emerg($e->__toString());
        }
    }

    /**
     * client register dependencies (parents)
     * it must register dependencies to change resources that were created by other clients
     *
     * @return array
     */
    public function getDependencies()
    {
        return [];
    }

    /**
     * get client version to install
     *
     * @return string
     */
    public function getDdlVersion()
    {
        return '0.2.3';
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
                $this->logger->err($e->__toString());
                $this->logger->err($nextE->__toString());
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
