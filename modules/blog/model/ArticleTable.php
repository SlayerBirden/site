<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace modules\blog\model;

use Maketok\Util\AbstractTableMapper;
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
}
