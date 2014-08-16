<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace modules\blog\model;

use Maketok\Util\AbstractTableMapper;
use Maketok\Util\Exception\ModelException;
use Zend\Db\Sql\Select;

class ArticleTable extends AbstractTableMapper
{

    /**
     * get ten most recent articles
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getTenMostRecent()
    {
        return $this->_tableGateway->select(function (Select $select) {
            $select
                ->order('created_at DESC')
                ->limit(10);
        });
    }

    /**
     * @param string $code
     * @return array|\ArrayObject|null
     * @throws \Maketok\Util\Exception\ModelException
     */
    public function findByCode($code)
    {
        $resultSet = $this->getGateway()->select(array('code' => $code));
        $row = $resultSet->current();
        if (!$row) {
            throw new ModelException(sprintf("Could not find row with identifier %s", $code));
        }
        return $row;
    }
}
