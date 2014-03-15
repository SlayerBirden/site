<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\App\Ddl;

use Zend\Db\Sql\Ddl\Column;
use Zend\Db\Sql\Ddl\Constraint;
use Zend\Db\Sql\Ddl\CreateTable;

class Installer
{

    /**
     * @param array $tableConfig
     */
    static private function _addTable(array $tableConfig)
    {
        foreach ($tableConfig as $tableName => $tableDefinition) {
            $table = new CreateTable($tableName);
            $_columns = isset($tableDefinition['columns']) ? $tableDefinition['columns'] : array();
            $_constraints = isset($tableDefinition['constraints']) ? $tableDefinition['constraints'] : array();
            foreach ($_columns as $columnName => $columnDefinition) {
                if (!isset($columnDefinition['type']) || is_int($columnName)) {
                    // can't create column without type or name
                    continue;
                }
                /** @var Column\ColumnInterface $type */
                $type = 'Column\\' . ucfirst($columnDefinition['type']);
                $length = isset($columnDefinition['length']) ? $columnDefinition['length'] : null;
                $column = new $type($columnName, $length);
                $table->addColumn($column);
            }
            foreach ($_constraints as $constraintType => $constraintDefinition) {
                /** @var Constraint\ConstraintInterface $type */
                $type = 'Constraint\\' . ucfirst($constraintType);

                $constraint = new $type($constraintDefinition[0]);
                $table->addConstraint($constraint);
            }
        }
    }

    /**
     * @param InstallerApplicableInterface $client
     */
    public function addClient($client)
    {
        $config = $client::getDdlConfig();
        $version = $client::getDdlConfigVersion();
    }
}