<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Procedure;

use Zend\Db\Sql\Ddl\DropTable as BaseDropTable;

class DropTable extends AbstractProcedure implements ProcedureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getQuery(array $args)
    {
        if (!isset($args[0])) {
            throw new \InvalidArgumentException("Not enough parameter to drop table.");
        }
        $tableName = $args[0];
        $table = new BaseDropTable($tableName);
        return $this->query($table);
    }
}
