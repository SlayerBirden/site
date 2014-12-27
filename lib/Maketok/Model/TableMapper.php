<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Model;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\Exception\ModelInfoException;
use Maketok\Util\Zend\Db\Sql\InsertIgnore;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Maketok\Util\Zend\Db\ResultSet\HydratingResultSet as ExtendedHydratingResultSet;

class TableMapper
{
    use UtilityHelperTrait;

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
     * @param string|string[]      $idField
     * @param string               $autoIncrement
     */
    public function __construct(AbstractTableGateway $tableGateway, $idField, $autoIncrement = null)
    {
        $this->tableGateway = $tableGateway;
        $this->idField = $idField;
        $this->autoIncrement = $autoIncrement;
    }

    /**
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     * @return string|string[]
     * @throws ModelException
     */
    public function idf()
    {
        return $this->getIdField();
    }

    /**
     * @codeCoverageIgnore
     * @return \Zend\Db\ResultSet\AbstractResultSet
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();

        return $resultSet;
    }

    /**
     * @codeCoverageIgnore
     * @param  array|\Closure|\Zend\Db\Sql\Predicate\PredicateInterface $filter
     * @return \Zend\Db\ResultSet\AbstractResultSet
     */
    public function fetchFilter($filter)
    {
        $resultSet = $this->tableGateway->select($filter);

        return $resultSet;
    }

    /**
     * @codeCoverageIgnore
     * @return AbstractTableGateway
     */
    public function getGateway()
    {
        return $this->tableGateway;
    }

    /**
     * @codeCoverageIgnore
     * @param  int|string|string[]     $id
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
     * @codeCoverageIgnore
     * delete entry by identifier
     * @param string|int|string[] $id
     */
    public function delete($id)
    {
        $this->getGateway()->delete($this->getIdFilter($id));
    }

    /**
     * get Filter
     * @param  int|string|string[] $data
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
     * @throws ModelException
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
                    // P.S. this is only viable for the models not implementing the "Lazy" interface
                    $insert = new InsertIgnore($this->getGateway()->getTable());
                    $insert->values($data);
                    $rowsAffected = $this->getGateway()->insertWith($insert);
                    if (!$rowsAffected) {
                        // at this step it means something is wrong with the app-db link
                        // or with app logic
                        throw new ModelException(sprintf("Model %s wasn't changed during save process.",
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
            // the info exception is silently burried here, as it serves merely a flow regulation
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
     * @param  object $model
     * @return array
     * @throws ModelException
     */
    protected function getModelData($model)
    {
        $resultSet = $this->getGateway()->getResultSetPrototype();
        if ($resultSet instanceof HydratingResultSet) {
            $hydrator = $resultSet->getHydrator();
            $data = $hydrator->extract($model);
        } else {
            throw new ModelException("Unknown object to handle.");
        }
        if ($model instanceof LazyModelInterface) {
            if (!count(array_diff_assoc($data, $model->processOrigin()))) {
                // well nothing was changed
                throw new ModelInfoException("Nothing was changed.");
            }
        }
        // do not proceed without data
        if (empty($data)) {
            throw new ModelException("Empty object data. Or invalid object to save.");
        }
        return $data;
    }

    /**
     * @codeCoverageIgnore
     * @throws ModelException
     * @return \ArrayObject|bool|mixed|null
     */
    public function getObjectPrototype()
    {
        $resultSet = $this->getGateway()->getResultSetPrototype();
        if ($resultSet instanceof ExtendedHydratingResultSet) {
            return clone $resultSet->getObjectPrototype();
        }
        throw new ModelException("Can't retrieve Object Prototype. Different Result Set is used.");
    }
}
