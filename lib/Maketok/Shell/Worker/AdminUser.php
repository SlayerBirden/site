<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Shell\Worker;

use Maketok\Authentication\Resource\Model\NewUser;
use Maketok\Firewall\AuthorizationInterface;
use Maketok\Model\TableMapper;
use Maketok\Shell\Installer;
use Maketok\Shell\NoArgumentException;
use Maketok\Util\Exception\ModelException;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class AdminUser extends AbstractWorker
{
    /**
     * @var TableMapper
     */
    private $roleTable;
    /**
     * @var TableMapper
     */
    private $userTable;
    /**
     * @var PasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param Installer $installer
     * @param TableMapper $roleTable
     * @param TableMapper $userTable
     * @param PasswordEncoderInterface $encoder
     */
    public function __construct(Installer $installer, TableMapper $roleTable, TableMapper $userTable, PasswordEncoderInterface $encoder)
    {
        $this->installer = $installer;
        $this->roleTable = $roleTable;
        $this->userTable = $userTable;
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        try {
            $role = $this->roleTable->find(AuthorizationInterface::ROLE_ADMINISTRATOR);
        } catch (ModelException $e) {
            $role = [
                'id' => AuthorizationInterface::ROLE_ADMINISTRATOR,
                'title' => 'admin',
                'created_at' => null,
                'updated_at' => null,
            ];
            $this->roleTable->save($role);
        }
        $user = new NewUser();
        foreach (['username', 'password', 'firstname', 'lastname'] as $key) {
            $val = $this->installer->getArg('admin_user_' . $key);
            if (empty($val)) {
                throw new NoArgumentException("Empty argument for '$key'");
            }
            $user->$key = $val;
        }
        $user->password_hash = $this->encoder->encodePassword($user->password, false);
        $user->roles = [$role];
        $this->userTable->save($user);
    }

    /**
     * @return string representation
     */
    public function __toString()
    {
        return 'admin_user';
    }
}
