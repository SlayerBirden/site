<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
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
}
