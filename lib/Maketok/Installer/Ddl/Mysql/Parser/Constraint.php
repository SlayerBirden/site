<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Parser;

class Constraint extends AbstractParser implements ParserInterface
{

    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        $row = trim($this->row);
        $constraintInfo = [];
        if (preg_match('/^(?:PRIMARY KEY|UNIQUE KEY).?(?:`(\S+)`)?.?\((\S+)\)/', $row, $matches)) {
            $constraintInfo = $this->parseKey($row, $matches);
        } elseif (preg_match('/^CONSTRAINT `(\S+)` FOREIGN KEY \(`(\S+)`\) REFERENCES `(\S+)` \(`(\S+)`\) ON DELETE (CASCADE|RESTRICT|SET NULL|NO ACTION) ON UPDATE (CASCADE|RESTRICT|SET NULL|NO ACTION)/', $row, $matches)) {
            $constraintInfo = $this->parseForeignKey($matches);
        }
        if ((isset($constraintInfo['name']) &&
                is_string($this->name) &&
                $constraintInfo['name'] == $this->name) ||
            (isset($constraintInfo['type']) &&
                $constraintInfo['type'] == 'primary' &&
                strtolower($this->name) == 'primary') ||
            is_null($this->name)) {
            return $constraintInfo;
        }
        return [];
    }

    /**
     * @param string $row
     * @param array $data
     * @return array
     */
    public function parseKey($row, $data)
    {
        $constraintInfo = array();
        if (strpos($row, 'PRIMARY') !== false) {
            $constraintInfo['type'] = 'primary';
        } else {
            $constraintInfo['type'] = 'unique';
            $constraintInfo['name'] = $data[1];
        }
        $definition = $data[2];
        $definition = explode(',', $definition);
        array_walk($definition, function(&$row) {
            $row = str_replace('`', '', $row);
        });
        $constraintInfo['definition'] = $definition;
        return $constraintInfo;
    }

    /**
     * @param array $data
     * @return array
     */
    public function parseForeignKey($data)
    {
        $constraintInfo = array();
        $constraintInfo['type'] = 'foreign_key';
        $constraintInfo['name'] = $data[1];
        $constraintInfo['column'] = $data[2];
        $constraintInfo['reference_table'] = $data[3];
        $constraintInfo['reference_column'] = $data[4];
        $constraintInfo['on_delete'] = $data[5];
        $constraintInfo['on_update'] = $data[6];
        return $constraintInfo;
    }
}
