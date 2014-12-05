<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Resource\Model;

use Maketok\Util\AbstractTableMapper;
use Maketok\Util\Exception\ModelException;

class DdlClientDependencyType extends AbstractTableMapper
{

    /**
     * @param $clientId
     * @param $dependencyCode
     * @return array|\ArrayObject|null
     * @throws ModelException
     */
    public function findByClientDependency($clientId, $dependencyCode)
    {
        $resultSet = $this->getGateway()->select(array(
            'client_id' => $clientId,
            'dependency_code' => $dependencyCode,
        ));
        $row = $resultSet->current();
        if (!$row) {
            throw new ModelException(sprintf("Could not find row with clientId %s and dependencyCode %s",
                $clientId,
                $dependencyCode
            ));
        }
        return $row;
    }
}
