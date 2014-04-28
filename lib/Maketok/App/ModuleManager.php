<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\App;

use Maketok\App\Ddl\Installer;
use Maketok\App\Ddl\InstallerApplicableInterface;
use Maketok\Module\ConfigInterface;
use Maketok\Observer\StateInterface;
use Maketok\Util\DirectoryHandler;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\TableGateway\TableGateway;

class ModuleManager extends TableGateway implements InstallerApplicableInterface
{

    private $_moduleDirs;
    private $_activeModules = [];
    private $_disabledModules = [];

    public function __construct()
    {
        parent::__construct('modules', Site::getAdapter(), new RowGatewayFeature('module_code'));
    }

    public function getModuleDirectories()
    {
        if (is_null($this->_moduleDirs)) {
            $handler = new DirectoryHandler();
            $this->_moduleDirs = $handler->ls($this->_getDir());
        }
        return $this->_moduleDirs;
    }

    /**
     * @return string
     */
    protected function _getDir()
    {
        return APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'modules';
    }

    /**
     * @return array
     */
    public static function getDdlConfig()
    {
        return [
            'modules' => [
                'columns' => [
                    'module_code' => [
                        'type' => 'varchar',
                        'length' => 32,
                    ],
                    'version' => [
                        'type' => 'varchar',
                        'length' => 15,
                    ],
                    'active' => [
                        'type' => 'integer',
                    ],
                    'updated_at' => [
                        'type' => 'datetime',
                    ],
                ],
                'constraints' => [
                    'primary' => [
                        'type' => 'primaryKey',
                        'def' => 'module_code',
                    ]
                ],
            ]
        ];
    }

    /**
     * @return string
     */
    public static function getDdlConfigVersion()
    {
        return '0.2.0';
    }

    /**
     * @return string
     */
    public static function getDdlConfigName()
    {
        return 'module_manager';
    }

    public function disableModule($code)
    {
        $this->update(array(
            array('active' => 0)
        ), array('module_code' => $code));
    }

    public function uninstallModule($code)
    {
        // TODO implement; depends on Installer
    }

    public function activateModule($code)
    {
        $resultSet = $this->select(array('module_code' => $code));
        $row = $resultSet->current();
        if (is_object($row)) {
            $row->active = true;
            $row->save();
        }
    }

    public function insertModule($code, ConfigInterface $config)
    {
        $resultSet = $this->select(array('module_code' => $code));
        $row = $resultSet->current();
        if (is_object($row)) {
            return;
        }
        $now = new \DateTime();
        $this->insert(array(
            'module_code' => $code,
            'version' => $config->getDdlConfigVersion(),
            'active' => $config->isActive(),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ));
    }

    public function processModuleConfig(StateInterface $state)
    {
        /** @var Installer $installer */
        $installer = $state->installer;
        foreach ($this->getModuleDirectories() as $dir) {
            if (file_exists($this->_getDir() . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'Config.php')) {
                require_once $this->_getDir() . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'Config.php';
                $className = "\\modules\\$dir\\Config";
                /** @var ConfigInterface $config */
                $config = new $className();
                if ($config->isActive()) {
                    array_push($this->_activeModules, $config);
                } else {
                    array_push($this->_disabledModules, $config);
                }
            }
        }
        // insert new modules
        foreach ($this->_activeModules as $config) {
            // insert module
            $this->insertModule($config->getCode(), $config);

        }
        // get real active ones
        array_filter($this->_activeModules, function($var) {
            $set = $this->select(array('module_code' => $var->getCode(), 'active' => 1));
            return $set->count() > 0;
        });
        // process active modules
        foreach ($this->_activeModules as $config) {
            // ddl
            $installer->addClient($config);
            // events
            $config->initListeners();
            // routes
            $config->initRoutes();
        }
    }

}