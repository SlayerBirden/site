<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Procedure;

use Zend\Db\Sql\Sql;

interface ProcedureInterface
{

    /**
     * set sql
     * @param Sql $sql
     */
    public function __construct(Sql $sql);

    /**
     * get Query
     * @param array $args
     * @return string
     */
    public function getQuery(array $args);
}
