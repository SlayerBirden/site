<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Zend\Db\Sql;


use Zend\Db\Sql\Insert;

class InsertIgnore extends Insert
{

    /**
     * @var array Specification array
     */
    protected $specifications = array(
        self::SPECIFICATION_INSERT => 'INSERT IGNORE INTO %1$s (%2$s) VALUES (%3$s)',
        self::SPECIFICATION_SELECT => 'INSERT IGNORE INTO %1$s %2$s %3$s',
    );
}
