<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Procedure;

use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Column\ColumnInterface;

class ChangeColumn extends AddColumn implements ProcedureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0]) || !isset($args[1]) || !isset($args[2]) || !isset($args[3])) {
            throw new \InvalidArgumentException("Not enough parameter to change column.");
        }
        $tableName = $args[0];
        $oldName = $args[1];
        $newName = $args[2];
        $newDefinition = $args[3];
        $table = new AlterTable($tableName);
        $column = $this->getInitColumn($newName, $newDefinition);
        $table->changeColumn($oldName, $column);
        return $this->query($table);
    }

}
