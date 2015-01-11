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

use Zend\Db\Sql\Ddl\AlterTable;

class DropColumn extends AbstractProcedure implements ProcedureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0]) || !isset($args[1])) {
            throw new \InvalidArgumentException("Not enough parameter to drop column.");
        }
        $tableName = $args[0];
        $columnName = $args[1];
        $table = $this->resource->alterTableFactory($tableName);
        $table->dropColumn($columnName);

        return $this->query($table);
    }
}
