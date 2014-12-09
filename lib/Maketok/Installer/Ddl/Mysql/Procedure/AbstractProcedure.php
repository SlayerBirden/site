<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Procedure;

use Zend\Db\Sql\Ddl\SqlInterface;
use Zend\Db\Sql\Sql;

abstract class AbstractProcedure implements ProcedureInterface
{
    /**
     * @var Sql
     */
    protected $sql;

    /**
     * {@inheritdoc}
     */
    public function __construct(Sql $sql)
    {
        $this->sql = $sql;
    }

    /**
     * @param SqlInterface $table
     * @return mixed|string
     */
    public function query(SqlInterface $table)
    {
        return $this->sql->getSqlStringForSqlObject($table);
    }

}
