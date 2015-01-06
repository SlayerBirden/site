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
use Maketok\Util\Exception\ModelException;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Select;

class UserTable extends TableMapper
{
    use ContainerTrait;

    /**
     * {@inheritdoc}
     */
    public function save(&$model)
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
        $data = [
            'user_id' => $model->id,
            'firstname' => $model->firstname,
            'lastname' => $model->lastname,
            'password_hash' => $model->password_hash,
            'updated_at' => null,
        ];
        $userDataTable->save($data);
    }

    /**
     * @param User $model
     * @throws \Maketok\Util\Exception\ModelException
     */
    public function handleRoles($model)
    {
        /** @var TableMapper $userRolesTable */
        $userRolesTable = $this->ioc()->get('auth_user_role_table');
        if (!$model->roles) {
            return;
        }
        foreach ($model->roles as $roleId) {
            $data = [
                'user_id' => $model->id,
                'role_id' => $roleId,
                'updated_at' => null,
            ];
            $userRolesTable->save($data);
        }
    }

    /**
     * @param string $username
     * @return User|null
     */
    public function getUserByUsername($username)
    {
        $select = $this->getJoinSelect();
        $select->where(['users.username' => $username]);
        $resultSet = $this->getGateway()->selectWith($select);
        if ($resultSet->count()) {
            return $resultSet->current();
        }
        return null;
    }

    /**
     * @param int $id
     * @return User|null
     * @throws ModelException
     */
    public function find($id)
    {
        $select = $this->getJoinSelect();
        $select->where(['users.id' => $id]);
        $resultSet = $this->getGateway()->selectWith($select);
        if ($resultSet->count()) {
            return $resultSet->current();
        }
        throw new ModelException(sprintf("Could not find row with identifier %s", json_encode($id)));
    }

    /**
     * @return null|\Zend\Db\ResultSet\ResultSetInterface
     */
    public function fetchAll()
    {
        return $this->getGateway()->selectWith($this->getJoinSelect());
    }

    /**
     * @return Select
     */
    protected function getJoinSelect()
    {
        $select = $this->getGateway()->getSql()->select();
        $select->join(
            'user_data',
            "users.id = user_data.user_id",
            ['firstname', 'lastname', 'password_hash', 'updated_at']
        )->join(
            'users_roles',
            "users.id = users_roles.user_id",
            ['roles' => new Expression("GROUP_CONCAT(users_roles.role_id)")],
            Select::JOIN_LEFT
        )->group('users.id');
        return $select;
    }
}
