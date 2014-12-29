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
use Zend\Db\Sql\Predicate\Expression;

class DdlClientHistoryType extends TableMapper
{
    /**
     * get MAX version for a client from history
     * @param int $clientId
     * @return string
     */
    public function getMaxVersion($clientId)
    {
        $select = $this->getGateway()
            ->getSql()
            ->select()
            ->where(['client_id' => $clientId])
            ->columns(['max_version' => new Expression('MAX(version)')])
            ->limit(1);
        /** @var \Zend\Db\ResultSet\ResultSet $resultSet */
        $resultSet = $this->tableGateway->selectWith($select);
        $row = $resultSet->current();
        return $row['max_version'];
    }
}
