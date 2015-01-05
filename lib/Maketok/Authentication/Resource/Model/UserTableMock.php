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

use Maketok\Model\TableMapper;

class UserTableMock extends TableMapper
{
    /**
     * @param string $username
     * @return User|null
     */
    public function getUserByUsername($username)
    {
        $resultSet = $this->fetchFilter(['username' => $username]);
        if ($resultSet->count()) {
            return $resultSet->current();
        }
        return null;
    }
}
