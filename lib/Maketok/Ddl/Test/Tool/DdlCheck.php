<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Ddl\Test\Tool;

use Maketok\App\Site;
use Zend\Db\Adapter\Adapter;

class DdlCheck
{
    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    private $_adapter;

    public function __construct()
    {
        $this->_adapter =  Site::getServiceContainer()->get('adapter');
    }

    /**
     * @param string $table
     * @return array
     */
    public function checkTable($table)
    {
        $result = $this->_adapter->query("SHOW CREATE TABLE `$table`", Adapter::QUERY_MODE_EXECUTE);
        $data = $result->current();
        $data = $data->getArrayCopy();
        $data = explode("\n", $data['Create Table']);
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
                $tableInfo['indexes'][] = $this->_parseIndex($row);
            } else {
                $tableInfo['columns'][] = $this->_parseColumn($row);
            }
        }
        return $tableInfo;
    }

    /**
     * @param string $row
     * @return array
     */
    protected function _parseColumn($row)
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
        return $columnInfo;
    }

    /**
     * @param string $row
     * @return array
     */
    protected function _parseIndex($row)
    {
        $indexInfo = array();
        preg_match('/^(?:KEY|INDEX) `(\S+)` \((\S+)\)/', trim($row), $matches);
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
        if (preg_match('/^(?:PRIMARY KEY|UNIQUE KEY).+\((\S+)\)/', $row, $matches)) {
            if (strpos($row, 'PRIMARY') !== false) {
                $constraintInfo['type'] = 'primary';
            } else {
                $constraintInfo['type'] = 'unique';
            }
            $definition = $matches[1];
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
            if (strpos($row, $column) !== false) {
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
        $result = $this->_adapter->query("SHOW CREATE TABLE `$table`", Adapter::QUERY_MODE_EXECUTE);
        $data = $result->current();
        $data = $data->getArrayCopy();
        $data = explode("\n", $data['Create Table']);
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
            if (strpos($row, "KEY `$index") !== false) {
                return $this->_parseIndex($row);
            }
        }
        return array();
    }

}
