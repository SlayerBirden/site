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

use Maketok\Util\ArrayValueTrait;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Constraint\ConstraintInterface;
use Zend\Db\Sql\Ddl\Index\Index;

class AddIndice extends AbstractProcedure implements ProcedureInterface
{
    use ArrayValueTrait;

    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0]) || !isset($args[1]) || !isset($args[2])) {
            throw new \InvalidArgumentException("Not enough parameter to add constraints.");
        }
        $tableName = $args[0];
        $indexName = $args[1];
        $indexDefinition = $args[2];
        $table = $this->getIfExists(3, $args, $this->resource->alterTableFactory($tableName));
        $table->addConstraint(new Index($indexDefinition['definition'], $indexName));

        return $this->query($table);
    }
}
