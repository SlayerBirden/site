<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\App;

use Maketok\App\Ddl\InstallerApplicableInterface;
use Maketok\Util\DirectoryHandler;
use Zend\Db\TableGateway\TableGateway;

class ModuleManager extends TableGateway implements InstallerApplicableInterface
{

    private $_moduleDirs;
    private $_activeModules;
    private $_moduleConfig;

    public function getModuleDirectories()
    {
        if (is_null($this->_moduleDirs)) {
            $path = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'modules';
            $handler = new DirectoryHandler();
            $this->_moduleDirs = $handler->ls($path);
        }
        return $this->_moduleDirs;
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
                        'type' => 'tinyint',
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

    }

    public function uninstallModule($code)
    {

    }
}