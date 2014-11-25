<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Resource\Model;

use Maketok\Installer\Exception;
use Maketok\Util\AbstractTableMapper;
use Maketok\App\Site;
use Maketok\Util\Exception\ModelException;

class DdlClientType extends AbstractTableMapper
{

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
        $dependencyType = Site::getServiceContainer()->get('ddl_client_dependency_table');
        foreach ($dependencies as $dependencyCode) {
            try {
                $dModel = $dependencyType->findByClientDependency($model->id, $dependencyCode);
            } catch (ModelException $e) {
                $dModel = Site::getServiceContainer()->get('ddl_client_dependency_model');
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
            $clientType = Site::getServiceContainer()->get('ddl_client_table');
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
            /** @var DdlClientHistoryType $historyType */
            $historyType = Site::getServiceContainer()->get('ddl_client_history_table');
            /** @var DdlClientHistory $historyModel */
            $historyModel = Site::getServiceContainer()->get('ddl_client_history_model');
            $historyModel->client_id = $model->id;
            $historyModel->prev_version = $oldVersion;
            $historyModel->version = $model->version;
            $historyModel->initializer = 'installer';
            $historyModel->created_at = date("Y-m-d H:i:s");
            $historyType->save($historyModel);
        }
    }
}
