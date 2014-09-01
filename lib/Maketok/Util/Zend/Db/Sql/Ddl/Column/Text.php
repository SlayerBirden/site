<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Zend\Db\Sql\Ddl\Column;

use Zend\Db\Sql\Ddl\Column\Column;

/**
 * Fix Text definitions: (as per http://dev.mysql.com/doc/refman/5.0/en/blob.html)
 * text can not have default value
 *
 * @package Maketok\Util\Zend\Db\Sql\Ddl\Column
 */
class Text extends Column
{

    /**
     * @var int
     */
    protected $length;

    /**
     * @var string Change type to text
     */
    protected $type = 'TEXT';

    /**
     * @param null $name
     * @param null|int $length
     * @param bool $nullable
     */
    public function __construct($name, $length = null, $nullable = false)
    {
        $this->setName($name);
        $this->setLength($length);
        $this->setNullable($nullable);
    }

    /**
     * @param  int $length
     * @return self
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
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

        // length
        if ($this->length) {
            $spec    .= '(%s)';
            $params[] = $this->length;
            $types[]  = self::TYPE_LITERAL;
        }

        // length
        if (!$this->isNullable) {
            $spec    .= ' %s';
            $params[] = 'NOT NULL';
            $types[]  = self::TYPE_LITERAL;
        }

        return array(array(
            $spec,
            $params,
            $types,
        ));
    }

}
