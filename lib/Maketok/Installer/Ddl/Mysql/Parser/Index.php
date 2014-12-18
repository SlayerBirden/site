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

class Index extends AbstractParser implements ParserInterface
{

    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        $indexInfo = [];
        preg_match('/^(?:KEY|INDEX) `(\S+)` \((\S+)\)/', trim($this->row), $matches);
        if (empty($matches)) {
            return $indexInfo;
        }
        $indexInfo['name'] = $matches[1];
        $definition = $matches[2];
        $definition = explode(',', $definition);
        array_walk($definition, function (&$row) {
            $row = str_replace('`', '', $row);
        });
        $indexInfo['definition'] = $definition;
        if (is_string($this->name) && ($indexInfo['name'] == $this->name) || is_null($this->name)) {
            return $indexInfo;
        }

        return [];
    }
}
