<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl\Resource\Model;

use Maketok\Model\TableMapper;
use Maketok\Util\Exception\ModelException;

class DdlClientDependencyType extends TableMapper
{

    /**
     * @param int $clientId
     * @param string $dependencyCode
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
