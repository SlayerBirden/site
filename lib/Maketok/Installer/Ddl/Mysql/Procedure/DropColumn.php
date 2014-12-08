<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Procedure;

use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Column\ColumnInterface;

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
        $table = new AlterTable($tableName);
        $table->dropColumn($columnName);
        return $this->query($table);
    }

}
