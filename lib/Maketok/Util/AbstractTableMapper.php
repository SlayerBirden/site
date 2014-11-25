<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;

use Maketok\Util\Exception\ModelException;
use Maketok\Util\Exception\ModelInfoException;
use Maketok\Util\Zend\Db\Sql\InsertIgnore;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;

abstract class AbstractTableMapper
{

    /**
     * @var \Zend\Db\TableGateway\TableGateway
     */
    protected $_tableGateway;

    /** @var  string */
    protected $_idFieldName;

    /**
     * @param TableGateway $tableGateway
     * @param string $idFieldName
     */
    public function __construct(TableGateway $tableGateway, $idFieldName)
    {
        $this->_tableGateway = $tableGateway;
        $this->_idFieldName = $idFieldName;
    }

    /**
     * @return string
     * @throws ModelException
     */
    public function getIdFieldName()
    {
        if (is_null($this->_idFieldName)) {
            throw new ModelException("Id Field Name not set.");
        }
        return $this->_idFieldName;
    }

    /**
     * alias
     * @return string
     */
    public function ifn()
    {
        return $this->getIdFieldName();
    }

    /**
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAll()
    {
        $resultSet = $this->_tableGateway->select();
        return $resultSet;
    }

    /**
     * @param array|\Closure|\Zend\Db\Sql\Where $filter
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchFilter($filter)
    {
        $resultSet = $this->_tableGateway->select($filter);
        return $resultSet;
    }

    /**
     * @return TableGateway
     */
    public function getGateway()
    {
        return $this->_tableGateway;
    }

    /**
     * @param int|string $id
     * @return array|\ArrayObject|null
     * @throws ModelException
     */
    public function find($id)
    {
        $resultSet = $this->getGateway()->select(array($this->ifn() => $id));
        $row = $resultSet->current();
        if (!$row) {
            throw new ModelException(sprintf("Could not find row with identifier %s", $id));
        }
        return $row;
    }

    /**
     * delete entry by identifier
     * @param string|int $id
     */
    public function delete($id)
    {
        $this->getGateway()->delete(array($this->ifn() => $id));
    }

    /**
     * @param object $model
     * @throws Exception\ModelInfoException
     */
    public function save($model)
    {
        $data = $this->_getModelData($model);
        // possible update
        if (array_key_exists('updated_at', $data)) {
            $data['updated_at'] = date("Y-m-d H:i:s");
        }
        // set created_at if it's not set
        if (array_key_exists('created_at', $data) && empty($data['created_at'])) {
            $data['created_at'] = date("Y-m-d H:i:s");
        }
        // now determine update or insert
        if (isset($data[$this->ifn()])) {
            $rowsAffected = $this->getGateway()->update($data, array($this->ifn() => $data[$this->ifn()]));
            if ($rowsAffected === 0) {
                // either no corresponding rows exist, so we need to insert
                // or data set is not updated compared to db entry
                // try to insert ignore
                $insert = new InsertIgnore($this->getGateway()->getTable());
                $insert->values($data);
                $rowsAffected = $this->getGateway()->insertWith($insert);
                if (!$rowsAffected) {
                    // questionable
//                    throw new ModelInfoException("Nothing got changed");
                } else {
                    $model->id = $this->getGateway()->getLastInsertValue();
                }
            }
        } else {
            $this->getGateway()->insert($data);
            $model->id = $this->getGateway()->getLastInsertValue();
        }
    }

    /**
     * @param $model
     * @return array
     * @throws ModelException
     */
    protected function _getModelData($model)
    {
        $resultSet = $this->getGateway()->getResultSetPrototype();
        if ($resultSet instanceof HydratingResultSet) {
            $hydrator = $resultSet->getHydrator();
            $data = $hydrator->extract($model);
        } elseif (method_exists($model, 'getData')) {
            $data = $model->getData();
        } else {
            $data = array();
        }
        // do not proceed without data
        if (empty($data)) {
            throw new ModelException("Empty object data. Or invalid object to save.");
        }
        return $data;
    }

}
