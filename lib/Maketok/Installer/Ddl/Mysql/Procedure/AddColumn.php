<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Procedure;

use Maketok\Util\ArrayValue;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Column\AbstractLengthColumn;
use Zend\Db\Sql\Ddl\Column\AbstractPrecisionColumn;
use Zend\Db\Sql\Ddl\Column\ColumnInterface;

class AddColumn extends AbstractProcedure implements ProcedureInterface
{
    use ArrayValue;
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
     * @return ColumnInterface
     */
    protected function getInitColumn($name, array $definition) {
        if (!isset($definition['type']) || is_int($name)) {
            // can't create column without type or name
            return false;
        }
        /** @var ColumnInterface $type */
        $type = '\\Zend\\Db\\Sql\\Ddl\\Column\\' . ucfirst($definition['type']);
        $nullable = $this->getIfExists('nullable', $definition, false);
        $default = $this->getIfExists('default', $definition);
        $length = $this->getIfExists('length', $definition);
        $digits = $this->getIfExists('digits', $definition);
        $decimal = $this->getIfExists('decimal', $definition);
        $options = array();
        $options['length'] = $this->getIfExists('length', $definition);
        $options['unsigned'] = $this->getIfExists('unsigned', $definition);
        $options['zero_fill'] = $this->getIfExists('zero_fill', $definition);
        $options['auto_increment'] = $this->getIfExists('auto_increment', $definition);
        $options['on_update'] = $this->getIfExists('on_update', $definition);
        if (is_subclass_of($type, 'Zend\Db\Sql\Ddl\Column\AbstractLengthColumn')) {
            $column = new $type($name, $length, $nullable, $default, $options);
        } elseif (is_subclass_of($type, 'Zend\Db\Sql\Ddl\Column\AbstractPrecisionColumn')) {
            $column = new $type($name, $digits, $decimal, $nullable, $default, $options);
        } else {
            $column = new $type($name, $nullable, $default, $options);
        }
        return $column;
    }
}
