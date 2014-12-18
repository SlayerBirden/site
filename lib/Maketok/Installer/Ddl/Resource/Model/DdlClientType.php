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

use Maketok\App\Helper\ContainerTrait;
use Maketok\Installer\Exception;
use Maketok\Model\TableMapper;
use Maketok\Util\Exception\ModelException;

class DdlClientType extends TableMapper
{
    use ContainerTrait;

    /**
     * @param string $code
     * @return array|\ArrayObject|null
     * @throws \Maketok\Installer\Exception
     */
    public function getClientByCode($code)
    {
        $resultSet = $this->getGateway()->select(array('code' => $code));
        $row = $resultSet->current();
        if (!$row) {
            throw new Exception(sprintf("Could not find client with code %s.", $code));
        }
        return $row;
    }

    /**
     * {@inheritdoc}
     *
     * also save dependency and history
     */
    public function save($model)
    {
        /** @var DdlClient $model */
        $dependencies = $model->dependencies;
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
            /** @var DdlClientDependency $dModel */
            $dModel->client_id = $model->id;
            $dModel->dependency_code = $dependencyCode;
            $dependencyType->save($dModel);
        }
        $oldVersion = null;
        if ($model->id) {
            // unfortunately for history we need old model version
            // to do that load model
            /** @var DdlClientType $clientType */
            $clientType = $this->ioc()->get('ddl_client_table');
            try {
                /** @var DdlClient $oldModel */
                $oldModel = $clientType->find($model->id);
                $oldVersion = $oldModel->version;
            } catch (ModelException $e) {
                // no old version
            }
        }
        parent::save($model);
        if ($oldVersion != $model->version) {
            /** @var TableMapper $historyType */
            $historyType = $this->ioc()->get('ddl_client_history_table');
            /** @var DdlClientHistory $historyModel */
            $historyModel = $historyType->getObjectPrototype();
            $historyModel->client_id = $model->id;
            $historyModel->prev_version = (is_null($oldVersion) ? '' : $oldVersion);
            $historyModel->version = $model->version;
            $historyModel->initializer = 'installer';
            $historyType->save($historyModel);
        }
    }

    /**
     * @param $model
     * @return array
     * @throws ModelException
     */
    protected function getModelData($model)
    {
        // hardcoded exclude columns
        $data = parent::getModelData($model);
        if (array_key_exists('config', $data)) {
            unset($data['config']);
        }
        if (array_key_exists('dependencies', $data)) {
            unset($data['dependencies']);
        }
        return $data;
    }
}
