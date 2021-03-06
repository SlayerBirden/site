<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl\Mysql\Procedure;

class ChangeColumn extends AddColumn implements ProcedureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0]) || !isset($args[1]) || !isset($args[2]) || !isset($args[3])) {
            throw new \InvalidArgumentException("Not enough parameter to change column.");
        }
        $tableName = $args[0];
        $oldName = $args[1];
        $newName = $args[2];
        $newDefinition = $args[3];
        $table = $this->resource->alterTableFactory($tableName);
        $column = $this->getInitColumn($newName, $newDefinition);
        $table->changeColumn($oldName, $column);

        return $this->query($table);
    }
}
