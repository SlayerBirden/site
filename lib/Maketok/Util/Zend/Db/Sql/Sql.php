<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util\Zend\Db\Sql;

use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\Sql as OriginalSql;

/**
 * @codeCoverageIgnore
 */
class Sql extends OriginalSql
{
    /**
     * reload in order to include special mods
     *
     * @param null $table
     * @param null $mode
     * @throws \Zend\Db\Sql\Exception\InvalidArgumentException
     * @return Insert
     */
    public function insert($table = null, $mode = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        if (!empty($mode)) {
            if (strtolower($mode) == 'ignore') {
                return new InsertIgnore(($table) ?: $this->table);
            } elseif (strtolower($mode) == 'duplicate') {
                return new InsertDuplicate(($table) ?: $this->table);
            }
        }
        return new Insert(($table) ?: $this->table);
    }
}
