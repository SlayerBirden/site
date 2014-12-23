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

/**
 * @codeCoverageIgnore
 */
class InsertIgnore extends Insert
{
    /**
     * @var array Specification array
     */
    protected $specifications = array(
        self::SPECIFICATION_INSERT => 'INSERT IGNORE INTO %1$s (%2$s) VALUES (%3$s)',
        self::SPECIFICATION_SELECT => 'INSERT IGNORE INTO %1$s %2$s %3$s',
    );
}
