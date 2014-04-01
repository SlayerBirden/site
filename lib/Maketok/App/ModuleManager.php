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
        $path = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'modules';
        $handler = new DirectoryHandler();
        return $handler->ls($path);
    }

    /**
     * @return array
     */
    public static function getDdlConfig()
    {
        return array(

        );
    }

    /**
     * @return string
     */
    public static function getDdlConfigVersion()
    {
        return '0.1.0';
    }

    /**
     * @return string
     */
    public static function getDdlConfigName()
    {
        return 'module_manager';
    }

    public function disableModule($name)
    {

    }

    public function uninstallModule($name)
    {

    }
}