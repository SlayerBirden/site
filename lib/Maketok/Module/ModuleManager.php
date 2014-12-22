<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Module;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\App\Site;
use Maketok\Installer\Ddl\ClientInterface;
use Maketok\Module\Resource\Model\Module;
use Maketok\Observer\State;
use Maketok\Model\TableMapper;
use Maketok\Util\ConfigGetter;
use Maketok\Util\Exception\ModelException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use Maketok\Installer;

class ModuleManager implements ClientInterface
{
    use UtilityHelperTrait;

    /**
     * @var TableMapper
     */
    protected $tableType;
    /**
     * @var ConfigInterface[]
     */
    private $modules = [];
    /**
     * @var ConfigInterface[]
     */
    private $activeModules;
    /**
     * @var Module[]
     */
    private $dbModules;
    /**
     * @var string[]
     */
    private $moduleDirs;
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
     * @param string      $configName
     * @param string      $area
     */
    public function __construct(TableMapper $tableType, $configName, $area)
    {
        $this->tableType = $tableType;
        $this->configName = $configName;
        $this->area = $area;
    }

    /**
     * @param  int|string|string[]            $id
     * @return Module|array|\ArrayObject|null
     * @throws ModelException
     */
    protected function initModule($id)
    {
        return $this->tableType->find($id);
    }

    /**
     * @param mixed $id
     * @param string $version
     */
    public function updateToVersion($id, $version)
    {
        try {
            $module = $this->initModule($id);
            $module->version = $version;
            $this->tableType->save($module);
        } catch (ModelException $e) {
            $this->getLogger()->err($e->__toString());
            $this->addSessionMessage('error', 'Could not update to version.');
        }
    }

    /**
     * @return array|null
     */
    public function getActiveModules()
    {
        if (is_null($this->activeModules) && !is_null($this->modules)) {
            /** @var Module[] $activeDbModulesResultSet */
            $activeDbModulesResultSet = $this->tableType->fetchFilter(array('active' => 1, 'area' => $this->area));
            $activeDbModuleCodes = [];
            foreach ($activeDbModulesResultSet as $module) {
                $activeDbModuleCodes[] = $module->module_code;
            }
            $this->activeModules = array_filter($this->modules, function ($config) use ($activeDbModuleCodes) {
                return in_array($config, $activeDbModuleCodes);
            });
        }

        return $this->activeModules;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @return Module[]
     */
    public function getDbModules()
    {
        if (is_null($this->dbModules)) {
            /** @var Module[] $result */
            $result = $this->tableType->fetchFilter(array('area' => $this->area));
            $this->dbModules = [];
            foreach ($result as $module) {
                $this->dbModules[$module->module_code] = $module;
            }
        }

        return $this->dbModules;
    }

    /**
     * @param  ConfigInterface $config
     * @return bool
     */
    public function getModuleExistsInDb(ConfigInterface $config)
    {
        $db = $this->getDbModules();

        return isset($db[(string) $config]);
    }

    /**
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getModuleDirectories()
    {
        if (is_null($this->moduleDirs)) {
            $this->moduleDirs = $this->ioc()->get('directory_handler')->ls($this->getDir());
        }

        return $this->moduleDirs;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getDir()
    {
        return Site::getConfig('modules_dir');
    }

    /**
     * @internal param StateInterface $state
     */
    public function processModuleConfig()
    {
        $configName = $this->configName;
        foreach ($this->getModuleDirectories() as $dir) {
            if (file_exists($this->getDir() . "/$dir/$configName.php")) {
                $className = "\\modules\\$dir\\$configName";
                /** @var ConfigInterface $config */
                $config = new $className();
                $this->modules[$config->getCode()] = $config;
            }
        }
        $this->getDispatcher()->notify('module_list_exists', new State(array(
            'modules' => $this->modules
        )));
    }

    /**
     * @param ConfigInterface $config
     */
    public function addDbModule(ConfigInterface $config)
    {
        /** @var Module $module */
        $module = $this->tableType->getGateway()->getResultSetPrototype()->getObjectPrototype();
        $module->module_code = $config->getCode();
        $module->active = $config->isActive();
        $module->version = $config->getVersion();
        $module->area = $this->area;
        $this->tableType->save($module);
    }

    /**
     * @param Module $module
     */
    public function removeDbModule(Module $module)
    {
        $this->tableType->delete($module->module_code);
    }

    /**
     * update Modules in current storage
     * @internal param StateInterface
     */
    public function updateModules()
    {
        if (empty($this->modules)) {
            return;
        }
        try {
            // candidates for addition
            foreach ($this->modules as $mConfig) {
                /** @var ConfigInterface $mConfig */
                if (!$this->getModuleExistsInDb($mConfig)) {
                    $this->addDbModule($mConfig);
                }
            }
            // candidates for deletion
            foreach ($this->getDbModules() as $module) {
                if (!isset($this->modules[$module->module_code])) {
                    $this->removeDbModule($module);
                }
            }
            $this->getDispatcher()->notify('modulemanager_updates_after',
                new State(array('active_modules' => $this->getActiveModules())));
        } catch (\Exception $e) {
            $this->getLogger()->emerg($e->__toString());
        }
    }

    /**
     * add installer subscribers
     * @internal param StateInterface
     * @codeCoverageIgnore
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
            $this->getLogger()->emerg($e->__toString());
        }
    }

    /**
     * @internal param StateInterface
     */
    public function processModules()
    {
        if (empty($this->modules)) {
            return;
        }
        try {
            // process active modules
            $this->getDispatcher()->notify('modulemanager_process_before',
                new State(array('active_modules' => $this->getActiveModules())));
            foreach ($this->getActiveModules() as $config) {
                // events
                /** @var ConfigInterface $config */
                $config->initListeners();
            }
            $this->getDispatcher()->notify('modulemanager_init_listeners_after',
                new State(array('active_modules' => $this->getActiveModules())));
            foreach ($this->getActiveModules() as $config) {
                // routes
                $config->initRoutes();
            }
            $this->getDispatcher()->notify('modulemanager_process_after',
                new State(array('active_modules' => $this->getActiveModules())));
        } catch (\Exception $e) {
            $this->getLogger()->emerg($e->__toString());
        }
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getDdlVersion()
    {
        return '0.2.3';
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getDdlConfig($version)
    {
        return current(ConfigGetter::getConfig(__DIR__.'/Resource/config/installer/ddl', $version));
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getDdlCode()
    {
        return 'module_manager';
    }
}
