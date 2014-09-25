<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql;

use Maketok\Installer\Ddl\Directives;
use Maketok\Installer\Ddl\ResourceInterface;
use Maketok\Installer\DirectivesInterface;
use Maketok\Installer\Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\DropTable;
use Zend\Db\Sql\Ddl\SqlInterface;
use Zend\Db\Sql\Sql;

class Resource implements ResourceInterface
{

    /** @var array */
    protected  $_availableConstraintTypes = ['primaryKey', 'uniqueKey', 'foreignKey'];

    /** @var \Zend\Db\Adapter\Adapter  */
    protected $_adapter;
    /**
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;
    /** @var array|\Iterator */
    private $_procedures;

    public function __construct(Adapter $adapter, Sql $sql)
    {
        $this->_adapter = $adapter;
        $this->sql = $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function getTable($table)
    {
        $data = $this->_getTableArray($table);
        if (empty($data)) {
            return [];
        }
        $fLine = array_shift($data);
        $lLine = array_pop($data);
        $tableInfo = array();
        $fLine = str_replace('CREATE TABLE `', '', $fLine);
        preg_match('/(\S+)`/', $fLine, $matches);
        $tableInfo['name'] = $matches[1];
        preg_match('/ENGINE=([a-zA-Z0-9]+)/', $lLine, $matches);
        $tableInfo['engine'] = $matches[1];
        preg_match('/DEFAULT CHARSET=([a-z0-9]+)/', $lLine, $matches);
        $tableInfo['default_charset'] = $matches[1];
        foreach ($data as $row) {
            if ((strpos($row, ' PRIMARY ') !== false) ||
                (strpos($row, ' UNIQUE ') !== false) ||
                (strpos($row, ' CONSTRAINT ') !== false)) {
                $tableInfo['constraints'][] = $this->_parseConstraint($row);
            } elseif ((strpos($row, '  KEY') !== false) ||
                (strpos($row, '  INDEX') !== false)) {
                $tableInfo['indices'][] = $this->_parseIndex($row);
            } else {
                $tableInfo['columns'][] = $this->_parseColumn($row);
            }
        }
        return $tableInfo;
    }

    /**
     * @param string $table
     * @return array
     */
    protected function _getTableArray($table)
    {
        try {
            $result = $this->_adapter->query(
                "SHOW CREATE TABLE `$table`", Adapter::QUERY_MODE_EXECUTE);
            $data = $result->current();
            $data = $data->getArrayCopy();
            $data = explode("\n", $data['Create Table']);
        } catch (\Exception $e) {
            // this is case with un-existing table
            $data = [];
        }
        return $data;
    }

    /**
     * @param string $table
     * @return array
     */
    protected function _getStrippedTableArray($table)
    {
        $data = $this->_getTableArray($table);
        array_shift($data);
        array_pop($data);
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($table, $column)
    {
        $data = $this->_getStrippedTableArray($table);
        foreach ($data as $row) {
            $res = $this->_parseColumn($row, $column);
            if (!empty($res)) {
                return $res;
            }
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraint($table, $constraint)
    {
        $data = $this->_getStrippedTableArray($table);
        foreach ($data as $row) {
            $res = $this->_parseConstraint($row, $constraint);
            if (!empty($res)) {
                return $res;
            }
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex($table, $index)
    {
        $data = $this->_getStrippedTableArray($table);
        foreach ($data as $row) {
            $res = $this->_parseIndex($row, $index);
            if (!empty($res)) {
                return $res;
            }
        }
        return [];
    }

    /**
     * @param string $row
     * @param null|string $name
     * @return array
     */
    protected function _parseColumn($row, $name = null)
    {
        $columnInfo = array();
        if (preg_match('/`(\S+)` (\w+)\((\d+)\) (.*)/', $row, $matches)) {
            $columnInfo['name'] = $matches[1];
            $columnInfo['type'] = $matches[2];
            $columnInfo['length'] = $matches[3];
            $other = $matches[4];
            if (strpos($other, 'NOT NULL') !== false) {
                $columnInfo['nullable'] = false;
            } else {
                $columnInfo['nullable'] = true;
            }
            if (strpos($other, 'AUTO_INCREMENT') !== false) {
                $columnInfo['auto_increment'] = true;
            }
            if (strpos($other, 'unsigned') !== false) {
                $columnInfo['unsigned'] = true;
            }
            if (($pos = strpos($other, 'DEFAULT')) !== false) {
                $columnInfo['default'] = substr($other, $pos + 9, -2);
            }
        } elseif (preg_match('/`(\S+)` (\w+) (.*)/', $row, $matches)) {
            $columnInfo['name'] = $matches[1];
            $columnInfo['type'] = $matches[2];
            $other = $matches[3];
            if (strpos($other, 'NOT NULL') !== false) {
                $columnInfo['nullable'] = false;
            } else {
                $columnInfo['nullable'] = true;
            }
            if (strpos($other, 'AUTO_INCREMENT') !== false) {
                $columnInfo['auto_increment'] = true;
            }
            if (strpos($other, 'unsigned') !== false) {
                $columnInfo['unsigned'] = true;
            }
            if (($pos = strpos($other, 'DEFAULT')) !== false) {
                $columnInfo['default'] = substr($other, $pos + 9, -2);
            }
        }
        if (is_string($name) && ($columnInfo['name'] == $name) || is_null($name)) {
            return $columnInfo;
        }
        return [];
    }

    /**
     * @param string $row
     * @param null|string $name
     * @return array
     */
    protected function _parseIndex($row, $name = null)
    {
        $indexInfo = [];
        preg_match('/^(?:KEY|INDEX) `(\S+)` \((\S+)\)/', trim($row), $matches);
        if (empty($matches)) {
            return $indexInfo;
        }
        $indexInfo['name'] = $matches[1];
        $definition = $matches[2];
        $definition = explode(',', $definition);
        array_walk($definition, function(&$row) {
            $row = str_replace('`', '', $row);
        });
        $indexInfo['definition'] = $definition;
        if (is_string($name) && ($indexInfo['name'] == $name) || is_null($name)) {
            return $indexInfo;
        }
        return [];
    }

    /**
     * @param string $row
     * @param null|string $name
     * @return array
     */
    protected function _parseConstraint($row, $name = null)
    {
        $row = trim($row);
        $constraintInfo = [];
        if (preg_match('/^(?:PRIMARY KEY|UNIQUE KEY).?(?:`(\S+)`)?.?\((\S+)\)/', $row, $matches)) {
            if (strpos($row, 'PRIMARY') !== false) {
                $constraintInfo['type'] = 'primary';
            } else {
                $constraintInfo['type'] = 'unique';
                $constraintInfo['name'] = $matches[1];
            }
            $definition = $matches[2];
            $definition = explode(',', $definition);
            array_walk($definition, function(&$row) {
                $row = str_replace('`', '', $row);
            });
            $constraintInfo['definition'] = $definition;
        } elseif (preg_match('/^CONSTRAINT `(\S+)` FOREIGN KEY \(`(\S+)`\) REFERENCES `(\S+)` \(`(\S+)`\) ON DELETE (CASCADE|RESTRICT|SET NULL|NO ACTION) ON UPDATE (CASCADE|RESTRICT|SET NULL|NO ACTION)/', $row, $matches)) {
            $constraintInfo['type'] = 'foreign_key';
            $constraintInfo['name'] = $matches[1];
            $constraintInfo['column'] = $matches[2];
            $constraintInfo['reference_table'] = $matches[3];
            $constraintInfo['reference_column'] = $matches[4];
            $constraintInfo['on_delete'] = $matches[5];
            $constraintInfo['on_update'] = $matches[6];
        }
        if ((isset($constraintInfo['name']) &&
                is_string($name) &&
                $constraintInfo['name'] == $name) ||
            (isset($constraintInfo['type']) &&
                $constraintInfo['type'] == 'primary' &&
                strtolower($name) == 'primary') ||
            is_null($name)) {
            return $constraintInfo;
        }
        return [];
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createProcedures(DirectivesInterface $directives)
    {
        if (isset($this->_procedures)) {
            throw new \LogicException("Wrong context of launching create procedures method. The procedures are already created.");
        }
        $this->_procedures = [];
        foreach ($directives as $type => $list) {
            switch ($type) {
                case 'addTables':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1])) {
                            throw new \InvalidArgumentException("Not enough parameter to add table.");
                        }
                        $this->_procedures[] = $this->_addTable($item[0], $item[1]);
                    }
                    break;
                case 'dropTables':
                    foreach ($list as $item) {
                        if (!isset($item[0])) {
                            throw new \InvalidArgumentException("Not enough parameter to drop table.");
                        }
                        $this->_procedures[] = $this->_dropTable($item[0]);
                    }
                    break;
                case 'addColumns':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1]) || !isset($item[2])) {
                            throw new \InvalidArgumentException("Not enough parameter to add columns.");
                        }
                        $this->_procedures[] = $this->_addColumn($item[0], $item[1], $item[2]);
                    }
                    break;
                case 'changeColumns':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1]) || !isset($item[2]) || !isset($item[3])) {
                            throw new \InvalidArgumentException("Not enough parameter to change column.");
                        }
                        $this->_procedures[] = $this->_changeColumn($item[0], $item[1], $item[2], $item[3]);
                    }
                    break;
                case 'dropColumns':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1])) {
                            throw new \InvalidArgumentException("Not enough parameter to drop table.");
                        }
                        $this->_procedures[] = $this->_dropColumn($item[0], $item[1]);
                    }
                    break;
                case 'addConstraints':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1]) || !isset($item[2])) {
                            throw new \InvalidArgumentException("Not enough parameter to add constraints.");
                        }
                        $this->_procedures[] = $this->_addConstraint($item[0], $item[1], $item[2]);
                    }
                    break;
                case 'dropConstraints':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1])) {
                            throw new \InvalidArgumentException("Not enough parameter to drop constraint.");
                        }
                        $this->_procedures[] = $this->_dropConstraint($item[0], $item[1]);
                    }
                    break;
                case 'addIndices':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1]) || !isset($item[2])) {
                            throw new \InvalidArgumentException("Not enough parameter to add index.");
                        }
                        $this->_procedures[] = $this->_addConstraint($item[0], $item[1], $item[2]);
                    }
                    break;
                case 'dropIndices':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1])) {
                            throw new \InvalidArgumentException("Not enough parameter to drop index.");
                        }
                        $this->_procedures[] = $this->_dropConstraint($item[0], $item[1]);
                    }
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function runProcedures()
    {
        if (is_null($this->_procedures)) {
            throw new \LogicException("Wrong context of using runProcedures. Procedures are not created yet.");
        }
        if (!(is_array($this->_procedures) ||
            (is_object($this->_procedures) && $this->_procedures instanceof \Iterator))) {
            throw new \LogicException("Unknown type of procedures.");
        }
        foreach ($this->_procedures as $query) {
            $this->_commit($query);
        }
    }

    /**
     * @param string $tableName
     * @param array $tableDefinition
     * @return mixed
     * @throws Exception
     */
    private function _addTable($tableName, array $tableDefinition)
    {
        $table = new CreateTable($tableName);
        if (!isset($tableDefinition['columns']) || !is_array($tableDefinition['columns'])) {
            throw new Exception(sprintf('Can not create a table `%s` without columns definition.', $tableName));
        }
        $_columns = $tableDefinition['columns'];
        $_constraints = isset($tableDefinition['constraints']) ? $tableDefinition['constraints'] : array();
        foreach ($_columns as $columnName => $columnDefinition) {
            $this->_addColumn($tableName, $columnName, $columnDefinition, $table);
        }
        foreach ($_constraints as $constraintName => $constraintDefinition) {
            $this->_addConstraint($tableName, $constraintName, $constraintDefinition, $table);
        }
        return $this->_getQuery($table);
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param array $columnDefinition
     * @param null|CreateTable|AlterTable $table
     * @return mixed
     */
    private function _addColumn($tableName, $columnName, array $columnDefinition, $table = null)
    {
        if (is_null($table)) {
            $table = new AlterTable($tableName);
        }
        $column = $this->_getInitColumn($columnName, $columnDefinition);
        $table->addColumn($column);
        return $this->_getQuery($table);
    }

    /**
     * @param string $tableName
     * @param string $constraintName
     * @param array $constraintDefinition
     * @param null|CreateTable|AlterTable $table
     * @return mixed
     * @throws Exception
     */
    private function _addConstraint($tableName, $constraintName, array $constraintDefinition, $table = null)
    {
        if (is_null($table)) {
            $table = new AlterTable($tableName);
        }
        if (!isset($constraintDefinition['type']) ||
            !in_array($constraintDefinition['type'], $this->_availableConstraintTypes)) {
            // can't create constraint or unavailable constraint type
            throw new Exception(
                sprintf('Can not create constraint %s for table %s. Missing or unavailable type.',
                    $constraintName,
                    $tableName)
            );
        }
        /** @var \Zend\Db\Sql\Ddl\Constraint\ConstraintInterface $type */
        $type = '\\Zend\\Db\\Sql\\Ddl\\Constraint\\' . ucfirst($constraintDefinition['type']);
        if ($constraintDefinition['type'] == 'index') {
            /** @var \Maketok\Util\Zend\Db\Sql\Ddl\Index\Index $type */
            $type = '\\Maketok\\Util\\Zend\\Db\\Sql\\Ddl\\Index\\Index';
        }
        if ($constraintDefinition['type'] == 'foreignKey') {
            $column = $constraintDefinition['def'];
            $refTable = $constraintDefinition['referenceTable'];
            $refColumn = $constraintDefinition['referenceColumn'];
            $onDelete = (isset($constraintDefinition['onDelete']) ? $constraintDefinition['onDelete'] : 'CASCADE');
            $onUpdate = (isset($constraintDefinition['onUpdate']) ? $constraintDefinition['onUpdate'] : 'CASCADE');
            $constraint = new $type($constraintName, $column, $refTable, $refColumn, $onDelete, $onUpdate);
        } else {
            $constraint = new $type($constraintDefinition['def'], $constraintName);
        }

        $table->addConstraint($constraint);
        return $this->_getQuery($table);
    }

    /**
     * @param string $query
     */
    private function _commit($query)
    {
        $adapter = $this->_adapter;
        $this->_adapter->query(
            $query,
            $adapter::QUERY_MODE_EXECUTE
        );
    }

    /**
     * @param string $tableName
     * @return mixed
     */
    private function _dropTable($tableName)
    {
        $table = new DropTable($tableName);
        return $this->_getQuery($table);
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @return mixed
     */
    private function _dropColumn($tableName, $columnName)
    {
        $table = new AlterTable($tableName);
        $table->dropColumn($columnName);
        return $this->_getQuery($table);
    }

    /**
     * @param string $tableName
     * @param string $constraintName
     * @return mixed
     */
    private function _dropConstraint($tableName, $constraintName)
    {
        $table = new AlterTable($tableName);
        $table->dropConstraint($constraintName);
        // big thanks to MySQL for this hack!!
        $query = $this->_getQuery($table);
        $query = str_replace('CONSTRAINT', 'FOREIGN KEY', $query);
        return $query;
    }

    /**
     * @param SqlInterface $table
     * @return mixed
     */
    private function _getQuery(SqlInterface $table)
    {
        return $this->sql->getSqlStringForSqlObject($table);
    }

    /**
     * @param string $tableName
     * @param string $oldName
     * @param string $newName
     * @param array $newDefinition
     * @return mixed
     */
    private function _changeColumn($tableName, $oldName, $newName, array $newDefinition)
    {
        $table = new AlterTable($tableName);
        $column = $this->_getInitColumn($newName, $newDefinition);
        $table->changeColumn($oldName, $column);
        return $this->_getQuery($table);
    }

    /**
     * @param string $name
     * @param array $definition
     * @return bool|\Zend\Db\Sql\Ddl\Column\ColumnInterface
     */
    private function _getInitColumn($name, array $definition) {
        if (!isset($definition['type']) || is_int($name)) {
            // can't create column without type or name
            return false;
        }
        /** @var \Zend\Db\Sql\Ddl\Column\ColumnInterface $type */
        $type = '\\Maketok\\Util\\Zend\\Db\\Sql\\Ddl\\Column\\' . ucfirst($definition['type']);
        if (!class_exists($type)) {
            // fallback
            $type = '\\Zend\\Db\\Sql\\Ddl\\Column\\' . ucfirst($definition['type']);
        }
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
                if (isset($definition['auto_increment'])) {
                    $options['auto_increment'] = $definition['auto_increment'];
                }
                $column = new $type($name, $nullable, $default, $options);
                break;
            case 'decimal':
            case 'float':
                $digits = isset($definition['digits']) ? $definition['digits'] : null;
                $decimal = isset($definition['decimal']) ? $definition['decimal'] : null;
                $options = array();
                if (isset($definition['unsigned'])) {
                    $options['unsigned'] = $definition['unsigned'];
                }
                $column = new $type($name, $digits, $decimal, $options);
                break;
            case 'blob':
            case 'text':
                $nullable = isset($definition['nullable']) ? $definition['nullable'] : false;
                $length = isset($definition['length']) ? $definition['length'] : null;
                $column = new $type($name, $length, $nullable);
                break;
            default:
                $column = new $type($name);
                break;
        }
        return $column;
    }
}
