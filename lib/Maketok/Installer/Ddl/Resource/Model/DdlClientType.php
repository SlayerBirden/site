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

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Installer\Exception;
use Maketok\Model\LazyModelInterface;
use Maketok\Model\TableMapper;
use Maketok\Util\Exception\ModelException;
use Zend\Db\Sql\Predicate\Expression;

class DdlClientType extends TableMapper
{
    use UtilityHelperTrait;

    /**
     * @param  string $code
     * @return DdlClient
     * @throws Exception
     */
    public function getClientByCode($code)
    {
        $resultSet = $this->fetchFilter(array('code' => $code));
        $row = $resultSet->current();
        if (!$row) {
            throw new Exception(sprintf("Could not find client with code %s.", $code));
        }
        return $this->afterFilters($row);
    }

    /**
     * {@inheritdoc}
     * @return DdlClient
     */
    public function find($id)
    {
        $row = parent::find($id);
        return $this->afterFilters($row);
    }

    /**
     * @param DdlClient $row
     * @return DdlClient
     */
    protected function afterFilters(DdlClient $row)
    {
        // dependency
        /** @var DdlClientDependencyType $dependencyType */
        $dependencyType = $this->ioc()->get('ddl_client_dependency_table');
        /** @var DdlClientDependency[] $dependencies */
        $dependencies = $dependencyType->fetchFilter(['id' => $row->id]);
        $codes = [];
        foreach ($dependencies as $dep) {
            $codes[] = $dep->dependency_code;
        }
        $row->setDependencies($codes);
        // max history
        /** @var DdlClientHistoryType $historyType */
        $historyType = $this->ioc()->get('ddl_client_history_table');
        $row->max_version = $historyType->getMaxVersion($row->id);
        return $row;
    }

    /**
     * {@inheritdoc}
     * @throws ModelException
     * also save dependency and history
     */
    public function save($model)
    {
        try {
            $this->getGateway()->getAdapter()->getDriver()->getConnection()->beginTransaction();
            parent::save($model);
            $this->handleDependencies($model);
            $this->handleVersion($model);
            $this->getGateway()->getAdapter()->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            // chaining throw
            $this->getGateway()->getAdapter()->getDriver()->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * handle client dependency
     * @param DdlClient $model
     * @throws ModelException
     */
    protected function handleDependencies(DdlClient $model)
    {
        $dependencies = $model->getDependencies();
        if (!$dependencies) {
            $dependencies = [];
        }
        /** @var DdlClientDependencyType $dependencyType */
        $dependencyType = $this->ioc()->get('ddl_client_dependency_table');
        foreach ($dependencies as $dependencyCode) {
            try {
                $dModel = $dependencyType->findByClientDependency($model->id, $dependencyCode);
            } catch (ModelException $e) {
                $dModel = $dependencyType->getObjectPrototype();
            }
            $dModel->client_id = $model->id;
            $dModel->dependency_code = $dependencyCode;
            $dependencyType->save($dModel);
        }
    }

    /**
     * @param DdlClient $model
     * @throws ModelException
     */
    protected function handleVersion(DdlClient $model)
    {
        $oldVersion = null;
        if ($model instanceof LazyModelInterface) {
            $origin = $model->processOrigin();
            $oldVersion = $this->getIfExists('version', $origin, null);
        } elseif ($model->id) {
            // if not using lazy objects we need to get the data from db
            try {
                $oldModel = $this->find($model->id);
                $oldVersion = $oldModel->version;
            } catch (ModelException $e) {
                // no old version
            }
        }
        if ($oldVersion != $model->version) {
            /** @var TableMapper $historyType */
            $historyType = $this->ioc()->get('ddl_client_history_table');
            $historyModel = [
                'client_id' => $model->id,
                'prev_version' => (is_null($oldVersion) ? '' : $oldVersion),
                'version' => $model->version,
                'initializer' => 'installer',
                'created_at' => null, //assign in table mapper
            ];
            $historyType->save($historyModel);
        }
    }

    /**
     * @return \Zend\Db\ResultSet\AbstractResultSet
     */
    public function fetchAllWithDependency()
    {
        $select = $this->getGateway()->getSql()->select();
        $select->join(
            'installer_ddl_client_history',
            'installer_ddl_client.id = installer_ddl_client_history.client_id',
            [
                'updated_at' => new Expression('MAX(created_at)'),
                'is_max_version' => new Expression('(MAX(installer_ddl_client_history.version) = installer_ddl_client.version)'),
            ],
            'left'
        )->join(
            'installer_ddl_client_dependency',
            'installer_ddl_client.id = installer_ddl_client_dependency.client_id',
            ['dependencies' => new Expression('GROUP_CONCAT(dependency_code)')],
            'left'
        )->group('installer_ddl_client.id');
        return $this->getGateway()->selectWith($select);
    }
}
