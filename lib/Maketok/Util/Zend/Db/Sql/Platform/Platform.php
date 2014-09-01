<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Zend\Db\Sql\Platform;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Platform\AbstractPlatform;
use Zend\Db\Sql\Platform\SqlServer;
use Zend\Db\Sql\Platform\Oracle;

/**
 * Class Platform
 * Create new platform class to extend Mysql platform in order to add AlterTable decorator
 * @package Maketok\Util\Zend\Db\Sql\Platform
 */
class Platform extends AbstractPlatform
{

    /**
     * @var AdapterInterface
     */
    protected $adapter = null;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        $platform = $adapter->getPlatform();
        switch (strtolower($platform->getName())) {
            case 'mysql':
                $platform = new Mysql\Mysql();
                $this->decorators = $platform->decorators;
                break;
            case 'sqlserver':
                $platform = new SqlServer\SqlServer();
                $this->decorators = $platform->decorators;
                break;
            case 'oracle':
                $platform = new Oracle\Oracle();
                $this->decorators = $platform->decorators;
                break;
            default:
        }
    }
}
