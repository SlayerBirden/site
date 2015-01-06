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

use Maketok\Authentication\IdentityInterface;

class User implements IdentityInterface
{
    /**
     * @var int (auto increment)
     */
    public $id;
    /**
     * @var string - identity
     */
    public $username;
    /**
     * @var string
     */
    public $firstname;
    /**
     * @var string
     */
    public $lastname;
    /**
     * @var string
     */
    public $password_hash;
    /**
     * @var \DateTime
     */
    public $created_at;
    /**
     * @var \DateTime
     */
    public $updated_at;
    /**
     * @var int[]
     */
    public $roles;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $confirm;

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * @param string $confirm
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;
    }
}
