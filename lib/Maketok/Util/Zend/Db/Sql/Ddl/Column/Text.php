<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Zend\Db\Sql\Ddl\Column;

use Zend\Db\Sql\Ddl\Column\Column;

/**
 * Fix Text definitions: (as per http://dev.mysql.com/doc/refman/5.0/en/blob.html)
 * text can not have length nor default value
 *
 * @package Maketok\Util\Zend\Db\Sql\Ddl\Column
 */
class Text extends Column
{

    /**
     * @var string Change type to blob
     */
    protected $type = 'TEXT';

    /**
     * @param null  $name
     * @param bool  $nullable
     */
    public function __construct($name, $nullable = false)
    {
        $this->setName($name);
        $this->setNullable($nullable);
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        $spec = $this->specification;

        $params   = array();
        $params[] = $this->name;
        $params[] = $this->type;

        $types = array(self::TYPE_IDENTIFIER, self::TYPE_LITERAL);

        if (!$this->isNullable) {
            $params[1] .= ' NOT NULL';
        }

        return array(array(
            $spec,
            $params,
            $types,
        ));
    }

}
