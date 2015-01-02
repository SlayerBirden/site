<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Authentication\Resource\Model;

use Maketok\App\Helper\ContainerTrait;
use Maketok\Model\TableMapper;

class UserTable extends TableMapper
{
    use ContainerTrait;

    /**
     * {@inheritdoc}
     */
    public function save($model)
    {
        try {
            $this->getGateway()->getAdapter()->getDriver()->getConnection()->beginTransaction();
            parent::save($model);
            $this->handleData($model);
            $this->handleRoles($model);
            $this->getGateway()->getAdapter()->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            // chaining throw
            $this->getGateway()->getAdapter()->getDriver()->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * save user data
     * @param User $model
     * @throws \Maketok\Util\Exception\ModelException
     */
    public function handleData($model)
    {
        /** @var TableMapper $userDataTable */
        $userDataTable = $this->ioc()->get('auth_user_data_table');
        $userDataTable->save([
            'user_id' => $model->id,
            'firstname' => $model->firstname,
            'lastname' => $model->lastname,
            'password_hash' => $model->password_hash,
            'updated_at' => null,
        ]);
    }

    /**
     * @param User $model
     * @throws \Maketok\Util\Exception\ModelException
     */
    public function handleRoles($model)
    {
        /** @var TableMapper $userRolesTable */
        $userRolesTable = $this->ioc()->get('auth_user_role_table');
        foreach ($model->roles as $roleId) {
            $userRolesTable->save([
                'user_id' => $model->id,
                'role_id' => $roleId,
                'updated_at' => null,
            ]);
        }
    }
}
