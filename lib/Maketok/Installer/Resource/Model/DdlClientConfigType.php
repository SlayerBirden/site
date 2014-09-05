<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Resource\Model;

use Maketok\Util\AbstractTableMapper;
use Zend\Db\Sql\Select;

class DdlClientConfigType extends AbstractTableMapper
{

    /**
     * @param string $code
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getAllConfigs($code)
    {
        return $this->fetchFilter(function (Select $select) use ($code) {
            $select->join(array('p' => 'installer_ddl_client'), "p.id = installer_ddl_client_config.client_id", [])
                ->where(array('p.code' => $code))->order('version ASC');
        });
    }

    /**
     * @param string $code
     * @return DdlClientConfig
     */
    public function getCurrentConfig($code)
    {
        $resultSet = $this->fetchFilter(function (Select $select) use ($code) {
            $select->join(array('p' => 'ddl_client'),
                "p.id = installer_ddl_client_config.client_id AND p.version = installer_ddl_client_config.version", [])
                ->where(array('p.code' => $code))->limit(1);
        });
        return $resultSet->current();
    }
}
