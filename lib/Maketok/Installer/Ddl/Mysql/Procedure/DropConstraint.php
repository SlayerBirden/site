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

class DropConstraint extends AbstractProcedure implements ProcedureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0]) || !isset($args[1]) || !isset($args[2])) {
            throw new \InvalidArgumentException("Not enough parameter to drop constraint.");
        }
        $tableName = $args[0];
        $constraintName = $args[1];
        $type = $args[2];
        $table = new AlterTable($tableName);
        $table->dropConstraint($constraintName);
        $query = $this->query($table);
        // big thanks to MySQL for this hack!!
        if ($type == 'foreign_key') {
            $query = str_replace('CONSTRAINT', 'FOREIGN KEY', $query);
        } elseif ($type == 'primary') {
            $query = str_replace('CONSTRAINT `primary`', 'PRIMARY KEY', $query);
        } elseif ($type == 'unique' || $type == 'index') {
            $query = str_replace('CONSTRAINT', 'INDEX', $query);
        }

        return $query;
    }
}
