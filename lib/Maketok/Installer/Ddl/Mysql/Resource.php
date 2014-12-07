<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql;

use Maketok\Installer\Ddl\ResourceInterface;
use Maketok\Installer\DirectivesInterface;
use Maketok\Installer\Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Column\ColumnInterface;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\DropTable;
use Zend\Db\Sql\Ddl\Index\Index;
use Zend\Db\Sql\Ddl\SqlInterface;
use Zend\Db\Sql\Sql;

class Resource implements ResourceInterface
{

    /** @var array */
    protected  $_availableConstraintTypes = ['primaryKey', 'uniqueKey', 'foreignKey', 'index'];

    /** @var \Zend\Db\Adapter\Adapter  */
    protected $_adapter;

    /** @var array  */
    protected $_typeMap = [
        'int' => 'integer',
    ];
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
        $data = $this->getTableArray($table);
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
                $constraint = $this->parseConstraint($row);
                if (isset($constraint['name'])) {
                    $tableInfo['constraints'][$constraint['name']] = $this->parseConstraint($row);
                } elseif($constraint['type'] == 'primary') {
                    $tableInfo['constraints']['primary'] = $this->parseConstraint($row);
                } else {
                    $tableInfo['constraints'][$this->getRandomName()] = $this->parseConstraint($row);
                }
            } elseif ((strpos($row, '  KEY') !== false) ||
                (strpos($row, '  INDEX') !== false)) {
                $index = $this->parseIndex($row);
                if (isset($index['name'])) {
                    $tableInfo['indices'][$index['name']] = $this->parseIndex($row);
                } else {
                    $tableInfo['indices'][$index['type']] = $this->parseIndex($row);
                }
            } else {
                $column = $this->parseColumn($row);
                $name = $column['name'];
                unset($column['name']);
                $tableInfo['columns'][$name] = $column;
            }
        }
        return $tableInfo;
    }

    /**
     * @return string
     */
    public function getRandomName()
    {
        return substr(uniqid('', true), -5);
    }

    /**
     * @param string $table
     * @return array
     */
    protected function getTableArray($table)
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
    protected function getStrippedTableArray($table)
    {
        $data = $this->getTableArray($table);
        array_shift($data);
        array_pop($data);
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($table, $column)
    {
        $data = $this->getStrippedTableArray($table);
        foreach ($data as $row) {
            $res = $this->parseColumn($row, $column);
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
        $data = $this->getStrippedTableArray($table);
        foreach ($data as $row) {
            $res = $this->parseConstraint($row, $constraint);
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
        $data = $this->getStrippedTableArray($table);
        foreach ($data as $row) {
            $res = $this->parseIndex($row, $index);
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
    protected function parseColumn($row, $name = null)
    {
        $columnInfo = array();
        if (preg_match('/`(\S+)` (\w+)\((\d+)\) (.*)/', $row, $matches)) {
            $columnInfo['name'] = $matches[1];
            $columnInfo['type'] = $this->convertType($matches[2]);
            $columnInfo['length'] = $matches[3];
            // hardcode for boolean type
            // which is fictional type, alias for tinyint(1)
            if ($columnInfo['type'] == 'tinyint' && $columnInfo['length'] == 1) {
                $columnInfo['type'] = 'boolean';
            }
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
            if (strpos($other, 'DEFAULT') !== false) {
                $columnInfo['default'] = $this->getDefault($other);
            }
        } elseif (preg_match('/`(\S+)` (\w+) (.*)/', $row, $matches)) {
            $columnInfo['name'] = $matches[1];
            $columnInfo['type'] = $this->convertType($matches[2]);
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
            if (strpos($other, 'DEFAULT') !== false) {
                $columnInfo['default'] = $this->getDefault($other);
            }
            if (strpos($other, 'ON UPDATE') !== false) {
                $columnInfo['on_update'] = 1;
            }
        }
        if (is_string($name) && ($columnInfo['name'] == $name) || is_null($name)) {
            return $columnInfo;
        }
        return [];
    }

    /**
     * @param string $string
     * @return null|string
     */
    protected function getDefault($string)
    {
        // trim comma from the end of a row
        $tok = strtok(trim($string, ','), " \n\t");
        $defaultNext = false;
        $result = null;
        while ($tok !== false) {
            if ($defaultNext) {
                $result = $tok;
                break;
            }
            if ($tok == 'DEFAULT') {
                $defaultNext = true;
            }
            $tok = strtok(" \n\t");
        }
        if ($result) {
            if ($result == 'NULL') {
                $result = null;
            } else {
                $result = trim($result, "\"'");
            }
        }
        return $result;
    }

    /**
     * @param string $type
     * @return string
     */
    public function convertType($type)
    {
        if (isset($this->_typeMap[$type])) {
            return $this->_typeMap[$type];
        }
        return $type;
    }

    /**
     * @param string $row
     * @param null|string $name
     * @return array
     */
    protected function parseIndex($row, $name = null)
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
    protected function parseConstraint($row, $name = null)
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
                        $this->_procedures[] = $this->addTable($item[0], $item[1]);
                    }
                    break;
                case 'dropTables':
                    foreach ($list as $item) {
                        if (!isset($item[0])) {
                            throw new \InvalidArgumentException("Not enough parameter to drop table.");
                        }
                        $this->_procedures[] = $this->dropTable($item[0]);
                    }
                    break;
                case 'addColumns':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1]) || !isset($item[2])) {
                            throw new \InvalidArgumentException("Not enough parameter to add columns.");
                        }
                        $this->_procedures[] = $this->addColumn($item[0], $item[1], $item[2]);
                    }
                    break;
                case 'changeColumns':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1]) || !isset($item[2]) || !isset($item[3])) {
                            throw new \InvalidArgumentException("Not enough parameter to change column.");
                        }
                        $this->_procedures[] = $this->changeColumn($item[0], $item[1], $item[2], $item[3]);
                    }
                    break;
                case 'dropColumns':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1])) {
                            throw new \InvalidArgumentException("Not enough parameter to drop table.");
                        }
                        $this->_procedures[] = $this->dropColumn($item[0], $item[1]);
                    }
                    break;
                case 'addConstraints':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1]) || !isset($item[2])) {
                            throw new \InvalidArgumentException("Not enough parameter to add constraints.");
                        }
                        $this->_procedures[] = $this->addConstraint($item[0], $item[1], $item[2]);
                    }
                    break;
                case 'dropConstraints':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1]) || !isset($item[2])) {
                            throw new \InvalidArgumentException("Not enough parameter to drop constraint.");
                        }
                        $this->_procedures[] = $this->dropConstraint($item[0], $item[1], $item[2]);
                    }
                    break;
                case 'addIndices':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1]) || !isset($item[2])) {
                            throw new \InvalidArgumentException("Not enough parameter to add index.");
                        }
                        $this->_procedures[] = $this->addConstraint($item[0], $item[1], $item[2]);
                    }
                    break;
                case 'dropIndices':
                    foreach ($list as $item) {
                        if (!isset($item[0]) || !isset($item[1])) {
                            throw new \InvalidArgumentException("Not enough parameter to drop index.");
                        }
                        $this->_procedures[] = $this->dropConstraint($item[0], $item[1], 'index');
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
            $this->commit($query);
        }
    }

    /**
     * @param string $tableName
     * @param array $tableDefinition
     * @return mixed
     * @throws Exception
     */
    private function addTable($tableName, array $tableDefinition)
    {
        $table = new CreateTable($tableName);
        if (!isset($tableDefinition['columns']) || !is_array($tableDefinition['columns'])) {
            throw new Exception(sprintf('Can not create a table `%s` without columns definition.', $tableName));
        }
        $_columns = $tableDefinition['columns'];
        $_constraints = isset($tableDefinition['constraints']) ? $tableDefinition['constraints'] : array();
        foreach ($_columns as $columnName => $columnDefinition) {
            $this->addColumn($tableName, $columnName, $columnDefinition, $table);
        }
        foreach ($_constraints as $constraintName => $constraintDefinition) {
            $this->addConstraint($tableName, $constraintName, $constraintDefinition, $table);
        }
        return $this->getQuery($table);
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param array $columnDefinition
     * @param null|CreateTable|AlterTable $table
     * @return mixed
     */
    private function addColumn($tableName, $columnName, array $columnDefinition, $table = null)
    {
        if (is_null($table)) {
            $table = new AlterTable($tableName);
        }
        $column = $this->getInitColumn($columnName, $columnDefinition);
        $table->addColumn($column);
        return $this->getQuery($table);
    }

    /**
     * @param string $tableName
     * @param string $constraintName
     * @param array $constraintDefinition
     * @param null|CreateTable|AlterTable $table
     * @return string
     * @throws Exception
     */
    private function addConstraint($tableName, $constraintName, array $constraintDefinition, $table = null)
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

        $table->addConstraint($constraint);
        return $this->getQuery($table);
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

    /**
     * @param string $query
     */
    private function commit($query)
    {
        $adapter = $this->_adapter;
        $this->_adapter->query(
            $query,
            $adapter::QUERY_MODE_EXECUTE
        );
    }

    /**
     * @param string $tableName
     * @return string
     */
    private function dropTable($tableName)
    {
        $table = new DropTable($tableName);
        return $this->getQuery($table);
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @return string
     */
    private function dropColumn($tableName, $columnName)
    {
        $table = new AlterTable($tableName);
        $table->dropColumn($columnName);
        return $this->getQuery($table);
    }

    /**
     * @param string $tableName
     * @param string $constraintName
     * @param string $type
     * @return string
     */
    private function dropConstraint($tableName, $constraintName, $type)
    {
        $table = new AlterTable($tableName);
        $table->dropConstraint($constraintName);
        $query = $this->getQuery($table);
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

    /**
     * @param SqlInterface $table
     * @return string
     */
    private function getQuery(SqlInterface $table)
    {
        return $this->sql->getSqlStringForSqlObject($table);
    }

    /**
     * @param string $tableName
     * @param string $oldName
     * @param string $newName
     * @param array $newDefinition
     * @return string
     */
    private function changeColumn($tableName, $oldName, $newName, array $newDefinition)
    {
        $table = new AlterTable($tableName);
        $column = $this->getInitColumn($newName, $newDefinition);
        $table->changeColumn($oldName, $column);
        return $this->getQuery($table);
    }

    /**
     * @param string $name
     * @param array $definition
     * @return bool|ColumnInterface
     */
    private function getInitColumn($name, array $definition) {
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

    /**
     * {@inheritdoc}
     */
    public function getProcedures()
    {
        return $this->_procedures;
    }
}
