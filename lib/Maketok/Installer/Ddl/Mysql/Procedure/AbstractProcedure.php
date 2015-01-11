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

use Maketok\Installer\Ddl\Mysql\Resource;
use Zend\Db\Sql\Ddl\SqlInterface;
use Zend\Db\Sql\Sql;

abstract class AbstractProcedure implements ProcedureInterface
{
    /**
     * @var Sql
     */
    protected $sql;
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * {@inheritdoc}
     */
    public function __construct(Sql $sql, Resource $resource)
    {
        $this->sql = $sql;
        $this->resource = $resource;
    }

    /**
     * @param  SqlInterface $table
     * @return mixed|string
     */
    public function query(SqlInterface $table)
    {
        return $this->sql->getSqlStringForSqlObject($table);
    }

    /**
     * get signature for query
     * @param  array $args
     * @return string
     */
    public function getQuerySignature(array $args)
    {
        return $args[0]; // tablename - for Alter parts
    }
}
