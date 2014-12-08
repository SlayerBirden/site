<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql;

use Maketok\Installer\Ddl\Mysql\Parser\Column;
use Maketok\Installer\Ddl\Mysql\Parser\Constraint;
use Maketok\Installer\Ddl\Mysql\Parser\Index as IndexParser;
use Maketok\Installer\Ddl\Mysql\Procedure\ProcedureInterface;
use Maketok\Installer\Ddl\ResourceInterface;
use Maketok\Installer\DirectivesInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Resource implements ResourceInterface
{
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
                $conParser = new Constraint($row);
                $constraint = $conParser->parse();
                if (isset($constraint['name'])) {
                    $tableInfo['constraints'][$constraint['name']] = $constraint;
                } elseif($constraint['type'] == 'primary') {
                    $tableInfo['constraints']['primary'] = $constraint;
                } else {
                    $tableInfo['constraints'][$this->getRandomName()] = $constraint;
                }
            } elseif ((strpos($row, '  KEY') !== false) ||
                (strpos($row, '  INDEX') !== false)) {
                $idxParser = new IndexParser($row);
                $index = $idxParser->parse();
                if (isset($index['name'])) {
                    $tableInfo['indices'][$index['name']] = $index;
                } else {
                    $tableInfo['indices'][$index['type']] = $index;
                }
            } else {
                $colParser = new Column($row);
                $column = $colParser->parse();
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
            $parser = new Column($row, $column);
            $res = $parser->parse();
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
            $parser = new Constraint($row, $constraint);
            $res = $parser->parse();
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
            $parser = new IndexParser($row, $index);
            $res = $parser->parse();
            if (!empty($res)) {
                return $res;
            }
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
            $className = $this->getProcedureClassNameFromType($type);
            /** @var ProcedureInterface $procedureClass */
            $procedureClass = new $className($this->sql);
            foreach ($list as $item) {
                $this->_procedures[] = $procedureClass->getQuery($item);
            }
        }
    }

    /**
     * @param $type
     * @return string
     */
    public function getProcedureClassNameFromType($type)
    {
        $prefix = 'Maketok\Installer\Ddl\Mysql\Procedure\\';
        if ($type == 'addIndices') {
            $base = 'AddConstraint';
        } elseif ($type == 'dropIndices') {
            $base = 'DropConstraint';
        } else {
            // ucfirst + strip last "s"
            $base = ucfirst(substr($type, 0, strlen($type) - 1));
        }

        return $prefix . $base;
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
     * {@inheritdoc}
     */
    public function getProcedures()
    {
        return $this->_procedures;
    }
}
