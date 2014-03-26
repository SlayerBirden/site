<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Sql\Ddl\Column;

use Zend\Db\Sql\Ddl\Column\Date;

class Datetime extends Date
{
    /**
     * @var string
     */
    protected $specification = '%s DATETIME %s %s';

}
