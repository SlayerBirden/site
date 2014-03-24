<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App\Ddl\Test\Tool;

use Maketok\App\Site;

class DdlCheck
{
    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    private $_adapter;

    public function __construct()
    {
        $this->_adapter =  Site::getAdapter();
    }

    /**
     * @param string $table
     */
    public function checkTable($table)
    {
        $result = $this->_adapter->query("SHOW CREATE TABLE `$table`");
        $data = $result->current();
        $data = explode("\n", $data);
        $fLine = array_shift($data);
        $lLine = array_pop($data);
        $tableInfo = array();
        $fLine = str_replace('CREATE TABLE `', '', $fLine);
        preg_match('/\S+/', $fLine, $matches);
        $tableInfo['name'] = $matches[0];
        preg_match('/ENGINE=([a-zA-Z0-9]+)/', $lLine, $matches);
        $tableInfo['engine'] = $matches[1];
        preg_match('/DEFAULT CHARSET=([a-z0-9]+)/', $lLine, $matches);
        $tableInfo['default_charset'] = $matches[1];
        foreach ($data as $row) {
            if ((strpos('PRIMARY', $row) !== false) ||
                (strpos('UNIQUE', $row) !== false) ||
                (strpos('CONSTRAINT', $row) !== false)) {
                $tableInfo['constraints'][] = $this->_parseConstraint($row);
            } elseif ((strpos('  KEY', $row) !== false) ||
                (strpos('  INDEX', $row) !== false)) {
                $tableInfo['indexes'][] = $this->_parseIndex($row);
            } else {
                $tableInfo['columns'][] = $this->_parseColumn($row);
            }
        }
    }

    /**
     * @param string $row
     * @return array
     */
    protected function _parseColumn($row)
    {
        $columnInfo = array();
        preg_match('/`(\S+)` (\w+)\((\d+)\) (.*)/', $row, $matches);
        $columnInfo['name'] = $matches[0];
        $columnInfo['type'] = $matches[1];
        $columnInfo['length'] = $matches[2];
        $other = $matches[3];
        if (strpos('NOT NULL', $other) !== false) {
            $columnInfo['nullable'] = false;
        } else {
            $columnInfo['nullable'] = true;
        }
        if (strpos('AUTO_INCREMENT', $other) !== false) {
            $columnInfo['auto_increment'] = true;
        }
        if (strpos('unsigned', $other) !== false) {
            $columnInfo['unsigned'] = true;
        }
        if (($pos = strpos('DEFAULT', $other)) !== false) {
            $subStr = substr($other, $pos + 8);
            $columnInfo['default'] = substr($subStr, -2);
        }
    }

    /**
     * @param string $row
     * @return array
     */
    protected function _parseIndex($row)
    {
        $indexInfo = array();
        preg_match('/^(?:KEY|INDEX) `(\S+)` \((\S+)/', trim($row), $matches);
        $indexInfo['name'] = $matches[1];
        $definition = $matches[2];
        $definition = explode(',', $definition);
        array_walk($definition, function(&$row) {
            $row = str_replace('`', '', $row);
        });
        $indexInfo['definition'] = $definition;
        return $indexInfo;
    }

    /**
     * @param string $row
     * @return array
     */
    protected function _parseConstraint($row)
    {
        $row = trim($row);
        $constraintInfo = array();
        if (preg_match('/^(?:PRIMARY KEY|UNIQUE KEY) \((\S+)/', $row, $matches)) {
            if (strpos($row, 'PRIMARY') !== false) {
                $constraintInfo['type'] = 'primary';
            } else {
                $constraintInfo['type'] = 'unique';
            }
            $definition = $matches[2];
            $definition = explode(',', $definition);
            array_walk($definition, function(&$row) {
                $row = str_replace('`', '', $row);
            });
            $constraintInfo['definition'] = $definition;
        } elseif (preg_match('/^CONSTRAINT `(\S+)` FOREIGN KEY \(`(\S+)`\) REFERENCES `(\S+) \(`(\S+)`\) ON DELETE (CASCADE|RESTRICT|SET NULL|NO ACTION) ON UPDATE (CASCADE|RESTRICT|SET NULL|NO ACTION)`/', $row, $matches)) {
            $constraintInfo['type'] = 'foreign_key';
            $constraintInfo['name'] = $matches[1];
            $constraintInfo['column'] = $matches[2];
            $constraintInfo['reference_table'] = $matches[3];
            $constraintInfo['reference_column'] = $matches[4];
            $constraintInfo['on_delete'] = $matches[5];
            $constraintInfo['on_update'] = $matches[6];
        }
        return $constraintInfo;
    }

    /**
     * @param string $table
     * @param string $column
     * @return array
     */
    public function checkColumn($table, $column)
    {
        $data = $this->_getPrepare($table);
        foreach ($data as $row) {
            if (strpos($column, $row) !== false) {
                return $this->_parseColumn($row);
            }
        }
        return array();
    }

    /**
     * @param string $table
     * @return array
     */
    protected function _getPrepare($table)
    {
        $result = $this->_adapter->query("SHOW CREATE TABLE `$table`");
        $data = $result->current();
        $data = explode("\n", $data);
        array_shift($data);
        array_pop($data);
        return $data;
    }

    /**
     * @param string $table
     * @param string $index
     * @return array
     */
    public function checkIndex($table, $index)
    {
        $data = $this->_getPrepare($table);
        foreach ($data as $row) {
            if (strpos($index, $row) !== false) {
                return $this->_parseIndex($row);
            }
        }
        return array();
    }

    /**
     * @param string $table
     * @param string $constraint
     * @return array
     */
    public function checkConstraint($table, $constraint)
    {
        $data = $this->_getPrepare($table);
        foreach ($data as $row) {
            if (strpos($constraint, $row) !== false) {
                return $this->_parseConstraint($row);
            }
        }
        return array();
    }
}