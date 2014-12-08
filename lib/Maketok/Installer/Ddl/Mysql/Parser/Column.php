<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
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
        } elseif (preg_match('/`(\S+)` (\w+) (.*)/', $this->row, $matches)) {
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
        if (is_string($this->name) && ($columnInfo['name'] == $this->name) || is_null($this->name)) {
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
        if (isset($this->typeMap[$type])) {
            return $this->typeMap[$type];
        }
        return $type;
    }
}
