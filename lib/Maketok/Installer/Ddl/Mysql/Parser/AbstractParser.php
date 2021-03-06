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

abstract class AbstractParser implements ParserInterface
{
    /**
     * @var string
     */
    protected $row;
    /**
     * @var null|string
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    public function __construct($row, $name = null)
    {
        $this->row = $row;
        $this->name = $name;
    }

    /**
     * @param string $columns
     * @return array
     */
    protected function parseColumns($columns)
    {
        $columns = explode(',', $columns);
        array_walk($columns, function (&$row) {
            $row = str_replace('`', '', trim($row));
        });
        if (count($columns) === 1) {
            $columns = reset($columns);
        }
        return $columns;
    }
}
