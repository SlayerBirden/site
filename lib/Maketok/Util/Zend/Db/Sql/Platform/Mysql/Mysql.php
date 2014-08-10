<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Zend\Db\Sql\Platform\Mysql;

use Maketok\Util\Zend\Db\Sql\Platform\Mysql\Ddl\AlterTableDecorator;
use Zend\Db\Sql\Platform\AbstractPlatform;
use Zend\Db\Sql\Platform\Mysql\Ddl\CreateTableDecorator;
use Zend\Db\Sql\Platform\Mysql\SelectDecorator;

class Mysql extends AbstractPlatform
{
    public function __construct()
    {
        $this->setTypeDecorator('Zend\Db\Sql\Select', new SelectDecorator());
        $this->setTypeDecorator('Zend\Db\Sql\Ddl\CreateTable', new CreateTableDecorator());
        $this->setTypeDecorator('Zend\Db\Sql\Ddl\AlterTable', new AlterTableDecorator());
    }
}
