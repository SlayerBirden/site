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
 * Fix Blob definitions: (as per http://dev.mysql.com/doc/refman/5.0/en/blob.html)
 * blob can not have default value
 *
 * @package Maketok\Util\Zend\Db\Sql\Ddl\Column
 */
class Blob extends Column
{

    /**
     * @var int
     */
    protected $length;

    /**
     * @var string Change type to blob
     */
    protected $type = 'BLOB';

    /**
     * Some of the parameters won't really taking part in expression (as of 2.4.X):
     * default and options
     * left for BC
     *
     * @param null  $name
     * @param int|null $length
     * @param bool  $nullable
     * @param null|string $default
     * @param array $options
     */
    public function __construct($name, $length = null, $nullable = false, $default = null, array $options = array())
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
