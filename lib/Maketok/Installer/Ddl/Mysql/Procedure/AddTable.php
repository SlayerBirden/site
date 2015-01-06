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

use Maketok\Installer\Exception;
use Maketok\Util\ArrayValueTrait;
use Zend\Db\Sql\Ddl\CreateTable;

class AddTable extends AbstractProcedure implements ProcedureInterface
{
    use ArrayValueTrait;

    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0]) || !isset($args[1])) {
            throw new \InvalidArgumentException("Not enough parameter to add table.");
        }
        $tableName = $args[0];
        $tableDefinition = $args[1];
        $table = new CreateTable($tableName);
        $columns = $this->getIfExists('columns', $tableDefinition, function () use ($tableName) {
            throw new Exception(sprintf('Can not create a table `%s` without columns definition.', $tableName));
        });
        $constraints = $this->getIfExists('constraints', $tableDefinition, []);
        $indices = $this->getIfExists('indices', $tableDefinition, []);
        foreach ($columns as $columnName => $columnDefinition) {
            $addCol = new AddColumn($this->sql, $this->resource);
            $addCol->getQuery(array($tableName, $columnName, $columnDefinition, $table));
        }
        foreach ($constraints as $constraintName => $constraintDefinition) {
            $addCon = new AddConstraint($this->sql, $this->resource);
            $addCon->getQuery(array($tableName, $constraintName, $constraintDefinition, $table));
        }
        foreach ($indices as $indexName => $indexDefinition) {
            // no special class for index
            $addCon = new AddConstraint($this->sql, $this->resource);
            $addCon->getQuery(array($tableName, $indexName, $indexDefinition, $table));
        }

        return $this->query($table);
    }

    /**
     * get signature for query
     * @param  array $args
     * @return string
     */
    public function getQuerySignature(array $args)
    {
        return $args[0] . md5(microtime(true));// must be unique
    }
}
