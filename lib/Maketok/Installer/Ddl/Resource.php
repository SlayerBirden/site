<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Zend\Db\Adapter\Adapter;

class Resource implements ResourceInterface
{

    /** @var \Zend\Db\Adapter\Adapter  */
    protected $_adapter;

    public function __construct(Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * {@inherited}
     */
    public function getTable($table)
    {
        $data = $this->_getTableArray($table);
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
        $result = $this->_adapter->query("SHOW CREATE TABLE `$table`", Adapter::QUERY_MODE_EXECUTE);
        $data = $result->current();
        $data = $data->getArrayCopy();
        $data = explode("\n", $data['Create Table']);
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
     * {@inherited}
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
     * {@inherited}
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
     * {@inherited}
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
        if (is_string($name) && ($constraintInfo['name'] == $name) ||
            ($constraintInfo['type'] == 'primary' && strtolower($name) == 'primary') ||
            is_null($name)) {
            return $constraintInfo;
        }
        return [];
    }
}
