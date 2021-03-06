<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl\Mysql\Parser;

use Maketok\Util\ArrayValueTrait;

class Constraint extends AbstractParser implements ParserInterface
{
    use ArrayValueTrait;
    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        $row = trim($this->row);
        $constraintInfo = [];
        if (preg_match('/^(?:PRIMARY KEY|UNIQUE KEY).?(?:`(\S+)`)?.?\(([a-z0-9`_ ,]+)\)/', $row, $matches)) {
            $constraintInfo = $this->parseKey($row, $matches);
        } elseif (preg_match('/^CONSTRAINT `(\S+)` FOREIGN KEY \(([a-z0-9`_ ,]+)\) REFERENCES `(\S+)` \(([a-z0-9`_ ,]+)\) ON DELETE (CASCADE|RESTRICT|SET NULL|NO ACTION) ON UPDATE (CASCADE|RESTRICT|SET NULL|NO ACTION)/', $row, $matches)) {
            $constraintInfo = $this->parseForeignKey($matches);
        }
        $cName = $this->getIfExists('name', $constraintInfo);
        $cType = $this->getIfExists('type', $constraintInfo);
        if (is_null($this->name) || $this->name == $cName) {
            return $constraintInfo;
        }
        if (strtolower($this->name) === 'primary' && 'primaryKey' == $cType) {
            return $constraintInfo;
        }
        return [];
    }

    /**
     * @param  string   $row
     * @param  string[] $data
     * @return array
     */
    public function parseKey($row, $data)
    {
        $constraintInfo = array();
        if (strpos($row, 'PRIMARY') !== false) {
            $constraintInfo['name'] = 'primary';
            $constraintInfo['type'] = 'primaryKey';
        } else {
            $constraintInfo['type'] = 'uniqueKey';
            $constraintInfo['name'] = $data[1];
        }
        $constraintInfo['definition'] = $this->parseColumns($data[2]);

        return $constraintInfo;
    }

    /**
     * @param  string[] $data
     * @return array
     */
    public function parseForeignKey($data)
    {
        $constraintInfo = array();
        $constraintInfo['type'] = 'foreignKey';
        $constraintInfo['name'] = $data[1];
        $constraintInfo['column'] = $this->parseColumns($data[2]);
        $constraintInfo['reference_table'] = $data[3];
        $constraintInfo['reference_column'] = $this->parseColumns($data[4]);
        $constraintInfo['on_delete'] = $data[5];
        $constraintInfo['on_update'] = $data[6];

        return $constraintInfo;
    }
}
