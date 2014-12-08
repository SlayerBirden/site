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

class AddColumn extends AbstractProcedure implements ProcedureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0]) || !isset($args[1]) || !isset($args[2])) {
            throw new \InvalidArgumentException("Not enough parameter to add columns.");
        }
        $tableName = $args[0];
        $columnName = $args[1];
        $columnDefinition = $args[2];
        $table = (isset($args[3]) ? $args[3] : new AlterTable($tableName));
        $column = $this->getInitColumn($columnName, $columnDefinition);
        $table->addColumn($column);
        return $this->query($table);
    }

    /**
     * @param string $name
     * @param array $definition
     * @return bool|ColumnInterface
     */
    protected function getInitColumn($name, array $definition) {
        if (!isset($definition['type']) || is_int($name)) {
            // can't create column without type or name
            return false;
        }
        /** @var ColumnInterface $type */
        $type = '\\Zend\\Db\\Sql\\Ddl\\Column\\' . ucfirst($definition['type']);
        switch ($definition['type']) {
            case 'char':
            case 'varchar':
                $nullable = isset($definition['nullable']) ? $definition['nullable'] : false;
                $default = isset($definition['default']) ? $definition['default'] : null;
                $length = isset($definition['length']) ? $definition['length'] : null;
                $column = new $type($name, $length, $nullable, $default);
                break;
            case 'bigInteger':
            case 'integer':
                $nullable = isset($definition['nullable']) ? $definition['nullable'] : false;
                $default = isset($definition['default']) ? $definition['default'] : null;
                $options = array();
                if (isset($definition['length'])) {
                    $options['length'] = $definition['length'];
                }
                if (isset($definition['unsigned'])) {
                    $options['unsigned'] = $definition['unsigned'];
                }
                if (isset($definition['zero_fill'])) {
                    $options['zero_fill'] = $definition['zero_fill'];
                }
                if (isset($definition['auto_increment'])) {
                    $options['auto_increment'] = $definition['auto_increment'];
                }
                $column = new $type($name, $nullable, $default, $options);
                break;
            case 'decimal':
            case 'float':
                $digits = isset($definition['digits']) ? $definition['digits'] : null;
                $decimal = isset($definition['decimal']) ? $definition['decimal'] : null;
                $nullable = isset($definition['nullable']) ? $definition['nullable'] : false;
                $default = isset($definition['default']) ? $definition['default'] : null;
                $options = array();
                if (isset($definition['unsigned'])) {
                    $options['unsigned'] = $definition['unsigned'];
                }
                if (isset($definition['zero_fill'])) {
                    $options['zero_fill'] = $definition['zero_fill'];
                }
                $column = new $type($name, $digits, $decimal, $nullable, $default, $options);
                break;
            case 'blob':
            case 'text':
                $nullable = isset($definition['nullable']) ? $definition['nullable'] : false;
                $length = isset($definition['length']) ? $definition['length'] : null;
                $column = new $type($name, $length, $nullable);
                break;
            case 'datetime':
            case 'timestamp':
                $nullable = isset($definition['nullable']) ? $definition['nullable'] : false;
                $default = isset($definition['default']) ? $definition['default'] : null;
                $options = array();
                if (isset($definition['on_update'])) {
                    $options['on_update'] = $definition['on_update'];
                }
                $column = new $type($name, $nullable, $default, $options);
                break;
            default:
                $nullable = isset($definition['nullable']) ? $definition['nullable'] : false;
                $default = isset($definition['default']) ? $definition['default'] : null;
                $column = new $type($name, $nullable, $default);
                break;
        }
        return $column;
    }
}
