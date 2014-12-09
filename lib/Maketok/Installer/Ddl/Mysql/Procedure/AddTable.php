<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Procedure;

use Maketok\Installer\Exception;
use Zend\Db\Sql\Ddl\CreateTable;

class AddTable extends AbstractProcedure implements ProcedureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0]) || !isset($args[1])) {
            throw new \InvalidArgumentException("Not enough parameter to add table.");
        }
        $tableName = $args[0];
        $tableDefinition = $args[1];
        $table = new CreateTable($tableName);
        if (!isset($tableDefinition['columns']) || !is_array($tableDefinition['columns'])) {
            throw new Exception(sprintf('Can not create a table `%s` without columns definition.', $tableName));
        }
        $_columns = $tableDefinition['columns'];
        $_constraints = isset($tableDefinition['constraints']) ? $tableDefinition['constraints'] : array();
        foreach ($_columns as $columnName => $columnDefinition) {
            $addCol = new AddColumn($this->sql);
            $addCol->getQuery(array($tableName, $columnName, $columnDefinition, $table));
        }
        foreach ($_constraints as $constraintName => $constraintDefinition) {
            $addCon = new AddConstraint($this->sql);
            $addCon->getQuery(array($tableName, $constraintName, $constraintDefinition, $table));
        }
        return $this->query($table);
    }
}
