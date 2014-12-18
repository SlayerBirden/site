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

use Maketok\Installer\Exception;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Constraint\ConstraintInterface;
use Zend\Db\Sql\Ddl\Index\Index;

class AddConstraint extends AbstractProcedure implements ProcedureInterface
{


    /** @var array */
    protected  $availableConstraintTypes = ['primaryKey', 'uniqueKey', 'foreignKey', 'index'];

    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0]) || !isset($args[1]) || !isset($args[2])) {
            throw new \InvalidArgumentException("Not enough parameter to add constraints.");
        }
        $tableName = $args[0];
        $constraintName = $args[1];
        $constraintDefinition = $args[2];
        $table = (isset($args[3]) ? $args[3] : new AlterTable($tableName));
        if (!isset($constraintDefinition['type']) ||
            !in_array($constraintDefinition['type'], $this->availableConstraintTypes)) {
            // can't create constraint or unavailable constraint type
            throw new Exception(
                sprintf('Can not create constraint %s for table %s. Missing or unavailable type.',
                    $constraintName,
                    $tableName)
            );
        }

        $table->addConstraint($this->getConstraint($constraintName, $constraintDefinition));
        return $this->query($table);
    }

    /**
     * @param string $constraintName
     * @param array $constraintDefinition
     * @return ConstraintInterface
     */
    public function getConstraint($constraintName, $constraintDefinition)
    {
        /** @var \Zend\Db\Sql\Ddl\Constraint\ConstraintInterface $type */
        $type = '\\Zend\\Db\\Sql\\Ddl\\Constraint\\' . ucfirst($constraintDefinition['type']);
        if ($constraintDefinition['type'] == 'foreignKey') {
            $column = $constraintDefinition['column'];
            $refTable = $constraintDefinition['reference_table'];
            $refColumn = $constraintDefinition['reference_column'];
            $onDelete = (isset($constraintDefinition['on_delete']) ? $constraintDefinition['on_delete'] : 'CASCADE');
            $onUpdate = (isset($constraintDefinition['on_update']) ? $constraintDefinition['on_update'] : 'CASCADE');
            $constraint = new $type($constraintName, $column, $refTable, $refColumn, $onDelete, $onUpdate);
        } elseif($constraintDefinition['type'] == 'index') {
            $constraint = new Index($constraintDefinition['definition'], $constraintName);
        } elseif($constraintDefinition['type'] == 'primaryKey') {
            $constraint = new $type($constraintDefinition['definition'], $this->getPKName($constraintDefinition['definition']));
        } else {
            $constraint = new $type($constraintDefinition['definition'], $constraintName);
        }
        return $constraint;
    }

    /**
     * @param string[] $def
     * @return string
     */
    public function getPKName($def)
    {
        $name = [];
        foreach ($def as $colName) {
            $name[] = $colName;
        }
        return implode('_', $name);
    }

}
