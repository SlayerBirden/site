<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;

use Maketok\App\Site;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\Exception\ModelInfoException;
use Maketok\Util\Zend\Db\Sql\InsertIgnore;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;

class TableMapper
{

    /**
     * @var \Zend\Db\TableGateway\AbstractTableGateway
     */
    protected $tableGateway;

    /** @var  string|string[] */
    protected $idField;

    /** @var null  */
    protected $autoIncrement;

    /**
     * @param AbstractTableGateway $tableGateway
     * @param string|string[] $idField
     * @param null $autoIncrement
     */
    public function __construct(AbstractTableGateway $tableGateway, $idField, $autoIncrement = null)
    {
        $this->tableGateway = $tableGateway;
        $this->idField = $idField;
        $this->autoIncrement = $autoIncrement;
    }

    /**
     * @return string|string[]
     * @throws ModelException
     */
    public function getIdField()
    {
        if (is_null($this->idField)) {
            throw new ModelException("Id Field Name not set.");
        }
        return $this->idField;
    }

    /**
     * alias
     * @return string|string[]
     * @throws ModelException
     */
    public function idf()
    {
        return $this->getIdField();
    }

    /**
     * @return \Zend\Db\ResultSet\AbstractResultSet
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    /**
     * @param array|\Closure|\Zend\Db\Sql\Predicate\PredicateInterface $filter
     * @return \Zend\Db\ResultSet\AbstractResultSet
     */
    public function fetchFilter($filter)
    {
        $resultSet = $this->tableGateway->select($filter);
        return $resultSet;
    }

    /**
     * @return AbstractTableGateway
     */
    public function getGateway()
    {
        return $this->tableGateway;
    }

    /**
     * @param int|string|string[] $id
     * @return array|\ArrayObject|null
     * @throws ModelException
     */
    public function find($id)
    {
        $resultSet = $this->getGateway()->select($this->getIdFilter($id));
        $row = $resultSet->current();
        if (!$row) {
            throw new ModelException(sprintf("Could not find row with identifier %s", json_encode($id)));
        }
        return $row;
    }

    /**
     * delete entry by identifier
     * @param string|int|string[] $id
     */
    public function delete($id)
    {
        $this->getGateway()->delete($this->getIdFilter($id));
    }

    /**
     * get Filter
     * @param int|string|string[] $data
     * @return string[]
     */
    protected function getIdFilter($data)
    {
        $id = $this->idf();
        if (!is_array($id)) {
            $id = array($id);
            if (!is_array($data)) {
                $data = array($this->idf() => $data);
            }
        }
        if (!is_array($data) && is_array($this->idf()) && (count($this->idf()) > 1)) {
            throw new \LogicException("Not enough data to get Filter.");
        }
        $filter = [];
        foreach ($id as $fieldName) {
            if (!isset($data[$fieldName])) {
                throw new \LogicException(sprintf("Missing data for id field %s", $fieldName));
            } else {
                $filter[$fieldName] = $data[$fieldName];
            }
        }
        return $filter;
    }

    /**
     * @param object $model
     */
    public function save($model)
    {
        try {
            $data = $this->getModelData($model);
            // possible update
            if (array_key_exists('updated_at', $data)) {
                $data['updated_at'] = date("Y-m-d H:i:s");
            }
            // set created_at if it's not set
            if (array_key_exists('created_at', $data) && empty($data['created_at'])) {
                $data['created_at'] = date("Y-m-d H:i:s");
            }
            // now determine update or insert
            if (is_null($this->autoIncrement) || (isset($data[$this->autoIncrement]))) {
                $rowsAffected = $this->getGateway()->update($data, $this->getIdFilter($data));
                if ($rowsAffected === 0) {
                    // either no corresponding rows exist, so we need to insert
                    // or data set is not updated compared to db entry
                    // try to insert ignore
                    $insert = new InsertIgnore($this->getGateway()->getTable());
                    $insert->values($data);
                    $rowsAffected = $this->getGateway()->insertWith($insert);
                    if (!$rowsAffected) {
                        // questionable
                        throw new ModelInfoException(sprintf("Model %s wasn't changed during save process.",
                            get_class($model)));
                    } else {
                        $this->assignIncrement($model);
                    }
                }
            } else {
                $this->getGateway()->insert($data);
                $this->assignIncrement($model);
            }
        } catch (ModelInfoException $e) {
            // Informative exceptions; for flow regulation
            Site::getSC()->get('logger')->debug($e->getMessage());
        }
    }

    /**
     * assign increment id if suitable
     * @param object $model
     */
    protected function assignIncrement($model)
    {
        $lastInsertedId = $this->getGateway()->getLastInsertValue();
        if ($lastInsertedId && ($increment = $this->autoIncrement)) {
            $model->$increment = $lastInsertedId;
        }
    }

    /**
     * @param object $model
     * @return array
     * @throws ModelException
     */
    protected function getModelData($model)
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
        if (method_exists($model, 'getOrigin')) {
            $originData = $model->getOrigin();
            $fullData = $data;
            foreach ($fullData as $key => $value) {
                if (isset($originData[$key]) && ($originData[$key] == $value)) {
                    unset($fullData[$key]);
                }
            }
            if (count($fullData) == 0) {
                // well nothing was changed
                throw new ModelException("Nothing was changed.");
            }
        }
        // do not proceed without data
        if (empty($data)) {
            throw new ModelException("Empty object data. Or invalid object to save.");
        }
        return $data;
    }

}
