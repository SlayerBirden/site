<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Parser;

class Column extends AbstractParser implements ParserInterface
{

    /** @var array  */
    protected $typeMap = [
        'int' => 'integer',
    ];

    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        $columnInfo = array();
        if (preg_match('/`(\S+)` (\w+)\((\d+)\) (.*)/', $this->row, $matches)) {
            $columnInfo = $this->parseLengthColumns($matches);
        } elseif (preg_match('/`(\S+)` (\w+) (.*)/', $this->row, $matches)) {
            $columnInfo = $this->parseNoLengthColumns($matches);
        }
        if (is_string($this->name) && ($columnInfo['name'] == $this->name) || is_null($this->name)) {
            return $columnInfo;
        }
        return [];
    }

    /**
     * @param array $data
     * @return array
     */
    public function parseLengthColumns($data)
    {
        $columnInfo = array();
        $columnInfo['name'] = $data[1];
        $columnInfo['type'] = $this->convertType($data[2]);
        $columnInfo['length'] = $data[3];
        // hardcode for boolean type
        // which is fictional type, alias for tinyint(1)
        if ($columnInfo['type'] == 'tinyint' && $columnInfo['length'] == 1) {
            $columnInfo['type'] = 'boolean';
        }
        $other = $data[4];
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
        return $columnInfo;
    }

    /**
     * @param array $data
     * @return array
     */
    public function parseNoLengthColumns($data)
    {
        $columnInfo = array();
        $columnInfo['name'] = $data[1];
        $columnInfo['type'] = $this->convertType($data[2]);
        $other = $data[3];
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
        return $columnInfo;
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
        if (isset($this->typeMap[$type])) {
            return $this->typeMap[$type];
        }
        return $type;
    }
}
