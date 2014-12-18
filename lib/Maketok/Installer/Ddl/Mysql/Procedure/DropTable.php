<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl\Mysql\Procedure;

use Zend\Db\Sql\Ddl\DropTable as BaseDropTable;

class DropTable extends AbstractProcedure implements ProcedureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0])) {
            throw new \InvalidArgumentException("Not enough parameter to drop table.");
        }
        $tableName = $args[0];
        $table = new BaseDropTable($tableName);
        return $this->query($table);
    }
}
