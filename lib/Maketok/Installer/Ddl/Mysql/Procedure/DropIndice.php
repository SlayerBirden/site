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

class DropIndice extends AbstractProcedure implements ProcedureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0]) || !isset($args[1])) {
            throw new \InvalidArgumentException("Not enough parameter to drop constraint.");
        }
        $tableName = $args[0];
        $constraintName = $args[1];
        $table = $this->resource->alterTableFactory($tableName);
        $table->dropIndex($constraintName);
        $query = $this->query($table);

        return $query;
    }
}
