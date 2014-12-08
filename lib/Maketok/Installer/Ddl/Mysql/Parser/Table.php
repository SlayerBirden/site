<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Parser;

use Maketok\Installer\Exception;

class Table
{
    /**
     * @var string[]
     */
    private $tableArray;

    /**
     * @param string[] $tableArray
     */
    public function __construct(array $tableArray)
    {

        $this->tableArray = $tableArray;
    }

    /**
     * @throws Exception
     * @return array
     */
    public function parse()
    {
        $data = $this->tableArray;
        if (empty($data)) {
            return [];
        }
        $tableInfo = $this->getBasicTableInfo($data);
        foreach ($data as $row) {
            $res = 0;
            $res = $res | $this->addConstraintInfo($tableInfo, $row);
            if (!$res) {
                $res = $res | $this->addIndexInfo($tableInfo, $row);
            }
            if (!$res) {
                $res = $res | $this->addColumnInfo($tableInfo, $row);
            }
            if (!$res) {
                throw new Exception(sprintf("Thr row %s wasn't executed by single parser.", $row));
            }
        }
        return $tableInfo;
    }

    /**
     * @param array $tableInfo
     * @param string $row
     * @return int
     */
    public function addConstraintInfo(&$tableInfo, $row)
    {
        $conParser = new Constraint($row);
        $constraint = $conParser->parse();
        if (empty($constraint)) {
            return 0;
        }
        if (isset($constraint['name'])) {
            $tableInfo['constraints'][$constraint['name']] = $constraint;
        } elseif($constraint['type'] == 'primary') {
            $tableInfo['constraints']['primary'] = $constraint;
        } else {
            $tableInfo['constraints'][$this->getRandomName()] = $constraint;
        }
        return 1;
    }

    /**
     * @param array $tableInfo
     * @param string $row
     * @return int
     */
    public function addIndexInfo(&$tableInfo, $row)
    {
        $idxParser = new Index($row);
        $index = $idxParser->parse();
        if (empty($index)) {
            return 0;
        }
        if (isset($index['name'])) {
            $tableInfo['indices'][$index['name']] = $index;
        } else {
            $tableInfo['indices'][$index['type']] = $index;
        }
        return 1;
    }

    /**
     * @param array $tableInfo
     * @param string $row
     * @return int
     */
    public function addColumnInfo(&$tableInfo, $row)
    {
        $colParser = new Column($row);
        $column = $colParser->parse();
        if (empty($column)) {
            return 0;
        }
        $name = $column['name'];
        unset($column['name']);
        $tableInfo['columns'][$name] = $column;
        return 1;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getBasicTableInfo(&$data)
    {
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
        return $tableInfo;
    }

    /**
     * @return string
     */
    public function getRandomName()
    {
        return substr(uniqid('', true), -5);
    }
}
